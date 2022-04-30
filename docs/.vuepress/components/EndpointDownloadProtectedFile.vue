<template>
  <endpoint
    name="Download Protected (Secured) File"
    method="GET"
    endpoint="f/{file-id}/download/s"
  >
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";

export default {
  name: "EndpointDownloadProtectedFile",
  components: { Endpoint },
};
</script>
