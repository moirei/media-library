<template>
  <endpoint name="Public File" method="GET" endpoint="f/{file-id}">
    <slot></slot>
  </endpoint>
</template>

<script>
import Endpoint from "./Endpoint";

export default {
  name: "EndpointPublicFile",
  components: { Endpoint },
};
</script>
