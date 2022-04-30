<template>
  <data-table :data="data" />
</template>

<script>
import DataTable from './DataTable'
import data from './endpoints/data/attachment'

export default {
  name: 'AttachmentData',
  components: { DataTable },
  data: () => ({ data }),
}
</script>