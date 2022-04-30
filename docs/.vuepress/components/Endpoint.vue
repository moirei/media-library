<template>
  <div>
    <h3>{{ name }}</h3>

    <p v-if="$slots.description" style="margin-bottom:20px">
      <slot name="description"></slot>
    </p>

    <p>
      Method: <code class="method" :class="[methodClass]">{{ method }}</code>
    </p>
    <p>
      Endpoint: <code>/media-library/{{ endpoint }}</code>
    </p>

    <template v-if="body">
      <h4>Body</h4>
      <data-table :data="body" />
    </template>

    <h4>Response</h4>
    <template v-if="responseIsLink">
      <router-link :to="response.route">{{ response.name }}</router-link>
    </template>
    <data-table v-else :data="response" />

    <div v-if="$slots.default">
      <h4>Example</h4>
      <slot></slot>
    </div>
  </div>
</template>

<script>
import DataTable from "./DataTable";
export default {
  name: "Endpoint",
  components: { DataTable },
  props: {
    name: String,
    method: String,
    endpoint: String,
    body: {},
    response: {},
  },
  computed: {
    responseIsLink() {
      return !!this.response?.route;
    },
    methodClass() {
      return String(this.method).toLowerCase();
    },
  },
};
</script>

<style>
.method {
  font-weight: 600;
}
.method.post {
  background: rgb(175, 255, 182) !important;
  color: green !important;
}
.method.get {
  background: #71b4f8 !important;
  color: #004080 !important;
}
.method.delete {
  background: #ffc4c2 !important;
  color: #91260b !important;
}
</style>
