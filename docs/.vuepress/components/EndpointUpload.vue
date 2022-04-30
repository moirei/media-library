<template>
  <endpoint
    name="Upload File"
    method="POST"
    endpoint="upload"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/upload'

export default {
  name: 'EndpointUpload',
  components: { Endpoint },
  data: () => ({ data })
}
</script>