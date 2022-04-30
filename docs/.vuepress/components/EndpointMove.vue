<template>
  <endpoint
    name="Move File"
    method="POST"
    endpoint="move/{file-id}"
    v-bind="data"
  >
    <template #description>
      Move file into the provided location. <br/>
      Location is the combination of the directory <code>location</code> and <code>name</code>.<br/>
      If the folder does not exist, one is created. If a folder ID is provided and it doesn't exist, the operation fails.
    </template>
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from './Endpoint'
import data from './endpoints/data/move'

export default {
  name: 'EndpointMove',
  components: { Endpoint },
  data: () => ({ data })
}
</script>