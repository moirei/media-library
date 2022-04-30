<template>
  <data-table :data="data" />
</template>

<script>
import DataTable from './DataTable'
import data from './endpoints/data/folder'

export default {
  name: 'FolderData',
  components: { DataTable },
  data: () => ({ data }),
}
</script>