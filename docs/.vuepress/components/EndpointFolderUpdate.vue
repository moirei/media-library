<template>
  <endpoint
    name="Update Folder"
    method="POST"
    endpoint="folder/update/{folder-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/update-folder'

export default {
  name: 'EndpointFolderUpdate',
  components: { Endpoint },
  data: () => ({ data })
}
</script>
