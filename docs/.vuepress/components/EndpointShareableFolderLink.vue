<template>
  <endpoint
    name="Get folder shareable link"
    method="POST"
    endpoint="shareable-link/folder/{file-id}"
    v-bind="data"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";
import data from "./endpoints/data/share";

export default {
  name: "EndpointShareableFolderLink",
  components: { Endpoint },
  data: () => ({ data }),
};
</script>
