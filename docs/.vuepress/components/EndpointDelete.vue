<template>
  <endpoint
    name="Delete File"
    method="DELETE"
    endpoint="delete/{file-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/delete'

export default {
  name: 'EndpointDelete',
  components: { Endpoint },
  data: () => ({ data })
}
</script>