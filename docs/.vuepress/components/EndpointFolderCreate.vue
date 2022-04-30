<template>
  <endpoint
    name="Create Folder"
    method="POST"
    endpoint="folder/create"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/create-folder'

export default {
  name: 'EndpointFolderCreate',
  components: { Endpoint },
  data: () => ({ data })
}
</script>
