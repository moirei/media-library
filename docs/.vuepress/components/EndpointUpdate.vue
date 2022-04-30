<template>
  <endpoint
    name="Update File"
    method="POST"
    endpoint="update/{file-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/update'

export default {
  name: 'EndpointUpdate',
  components: { Endpoint },
  data: () => ({ data }),
}
</script>