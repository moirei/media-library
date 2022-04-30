<template>
  <endpoint
    name="Purge an attachment"
    method="DELETE"
    endpoint="attachment/{url|attachment-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/delete-attachment'

export default {
  name: 'EndpointAttachmentDelete',
  components: { Endpoint },
  data: () => ({ data })
}
</script>