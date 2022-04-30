<template>
  <endpoint
    name="Download Public File"
    method="GET"
    endpoint="f/{file-id}/download"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";

export default {
  name: "EndpointDownloadPublicFile",
  components: { Endpoint },
};
</script>
