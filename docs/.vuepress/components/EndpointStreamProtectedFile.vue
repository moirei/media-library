<template>
  <endpoint
    name="Stream Protected (Secured) File"
    method="GET"
    endpoint="f/{file-id}/stream/s"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";

export default {
  name: "EndpointStreamProtectedFile",
  components: { Endpoint },
};
</script>
