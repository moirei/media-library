<template>
  <endpoint
    name="Get file shareable link"
    method="POST"
    endpoint="shareable-link/{file-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";
import data from "./endpoints/data/share";

export default {
  name: "EndpointShareableLink",
  components: { Endpoint },
  data: () => ({ data }),
};
</script>
