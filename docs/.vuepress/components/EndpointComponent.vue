<template>
  <component :is="componentName">
    <slot></slot>
  </component>
</template>

<script>
export default {
  name: 'EndpointComponent',
  props: {
    name: { type: String, required: true },
  },
  data: () => ({
    dialog: false,
    tab: null,
  }),
  computed: {
    componentName(){
      return `endpoint-${this.name}`
    }
  }
}
</script>
