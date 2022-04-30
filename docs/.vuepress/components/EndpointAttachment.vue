<template>
  <endpoint
    name="Upload Attachment"
    method="POST"
    endpoint="attachment"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/attachment-upload'

export default {
  name: 'EndpointAttachment',
  components: { Endpoint },
  data: () => ({ data }),
}
</script>