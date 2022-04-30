<template>
  <endpoint
    name="Protected (Secured) File"
    method="GET"
    endpoint="f/{file-id}/s"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";

export default {
  name: "EndpointProductedFile",
  components: { Endpoint },
};
</script>
