<template>
  <endpoint
    name="Stream Public File"
    method="GET"
    endpoint="f/{file-id}/stream"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";

export default {
  name: "EndpointStreamPublicFile",
  components: { Endpoint },
};
</script>
