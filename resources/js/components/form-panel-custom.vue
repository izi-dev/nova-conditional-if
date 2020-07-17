<template>
    <div v-if="panel.fields.length > 0">
        <heading :level="1" :class="panel.helpText ? 'mb-2' : 'mb-3'">
            {{ panel.name }}
        </heading>

        <p
            v-if="panel.helpText"
            class="text-80 text-sm font-semibold italic mb-3"
            v-html="panel.helpText"
        ></p>

        <card>
            <component
                :class="{
          'remove-bottom-border': index == panel.fields.length - 1,
        }"
                v-for="(field, index) in panel.fields"
                :key="index"
                v-if="getVisibleComponent(field)"
                :is="`${mode}-${field.component}`"
                :errors="validationErrors"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :field="field"
                :via-resource="viaResource"
                :via-resource-id="viaResourceId"
                :via-relationship="viaRelationship"
                :shown-via-new-relation-modal="shownViaNewRelationModal"
                @file-deleted="$emit('update-last-retrieved-at-timestamp')"
                @file-upload-started="$emit('file-upload-started')"
                @file-upload-finished="$emit('file-upload-finished')"
            />
        </card>
    </div>
</template>

<script>
    export default {

        props: {
            shownViaNewRelationModal: {
                type: Boolean,
                default: false,
            },

            panel: {
                type: Object,
                required: true,
            },

            name: {
                default: 'Panel',
            },

            mode: {
                type: String,
                default: 'form',
            },

            fields: {
                type: Array,
                default: [],
            },

            validationErrors: {
                type: Object,
                required: true,
            },

            resourceName: {
                type: String,
                required: true,
            },

            resourceId: {
                type: [Number, String],
            },

            viaResource: {
                type: String,
            },

            viaResourceId: {
                type: [Number, String],
            },

            viaRelationship: {
                type: String,
            },
        },
        data() {
            return {
                visibleFields: []
            }
        },
        mounted() {
            this.fillVisibleFields();

            this.watchedComponents
                .forEach(component => {
                    component.$watch(
                        this.findWatchableComponentAttribute(component),
                        (value) => {
                            this.update();
                        },
                        {immediate: true}
                    );
                })
        },

        methods: {
            findWatchableComponentAttribute(component) {
                let attribute;

                switch (component.field.component) {
                    case 'belongs-to-many-field':
                    case 'belongs-to-field':
                        attribute = 'selectedResource';
                        break;
                    case 'morph-to-field':
                        attribute = 'fieldTypeName';
                        break;
                    default:
                        attribute = 'value';
                }
                return attribute;
            },
            componentIsDependency(component) {
                if (component.field === undefined) return false;

                return this.getFieldsDependency.filter(field => component.field.attribute === (field)).length !== 0;
            },
            async update() {
                let vm = this;

                vm.fields
                    .filter(field => field.dependsOn != undefined)
                    .forEach(field => {
                        let values = field.dependsOn.map(attribute => vm.getValueComponentDependency(attribute));
                        this.executeValidation(values, field);
                    });
            },
            executeValidation(values, field) {
                let vm = this;

                if (field.condition === undefined) {
                    vm.executeValitadionCallable(values, field);
                    return;
                }

                if (typeof field.condition === 'string') {
                    vm.visibleFields.find(model => model.attribute == field.attribute).visible = vm.executeValidationString(values, field);
                    return;
                }
            },
            executeValidationString(values, field) {

                let vars = values.map(item => {
                    return {
                        "field" : "_value." + item.attribute,
                        "value" : item.value
                    }
                });

                let condition = field.condition;

                for (let i = 0; i < vars.length; i++) {
                    if(condition.includes(vars[i].field)) {
                        condition = condition.replace(vars[i].field, `'${vars[i].value}'`)
                    }
                }

                return eval(condition);
            },
            executeValitadionCallable(values, field) {
                let vm = this;

                Nova.request()
                    .post(`/nova-vendor/conditional-field/condition/${vm.resourceName}`, {
                        attribute: field.attribute,
                        values: values,
                        resourceId: this.resourceId,
                    })
                    .then(function (data) {
                        vm.visibleFields.find(model => model.attribute == field.attribute).visible = data.data.result;
                    });
            },
            getValueComponentDependency(attribute) {
                let component = this.watchedComponents.find(component => component.field.attribute === attribute);

                return {
                    'attribute': component.field.attribute,
                    'value': component.value
                };
            },
            getVisibleComponent(field) {
                let visibleFields = this.visibleFields.find(data => data.attribute == field.attribute);

                return visibleFields == null ? true : visibleFields.visible;
            },
            fillVisibleFields() {
                this.fields.forEach(field => {
                    this.visibleFields.push({
                        attribute: field.attribute,
                        visible: field.dependsOn != undefined ? false : true
                    });
                });
            }
        },
        computed: {
            watchedComponents() {
                let parent = this.$children.find(component => component.$children.length != 0)

                if (parent == null) {
                    return [];
                }

                return parent.$children.filter(component => this.componentIsDependency(component));
            },
            getFieldsDependency() {
                let fields = [];

                this.fields.filter(function (field) {
                    return field.dependsOn != undefined;
                }).forEach(function (field) {
                    fields = [...field.dependsOn, ...fields];
                });

                return fields;
            },
        },
    }
</script>
