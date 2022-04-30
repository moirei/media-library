<template>
  <endpoint
    name="Browse directory location"
    method="POST"
    endpoint="browse"
    v-bind="data"
  >
    <template #description>
      Return the folders and files in the given location
    </template>
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/browse'

export default {
  name: 'EndpointBrowse',
  components: { Endpoint },
  data: () => ({ data })
}
</script>