<template>
  <data-table :data="data" />
</template>

<script>
import DataTable from './DataTable'
import data from './endpoints/data/file'

export default {
  name: 'FileData',
  components: { DataTable },
  data: () => ({ data }),
}
</script>