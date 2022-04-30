<template>
  <endpoint
    name="Delete Folder"
    method="DELETE"
    endpoint="folder/delete/{folder-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/delete-folder'

export default {
  name: 'EndpointFolderDelete',
  components: { Endpoint },
  data: () => ({ data })
}
</script>