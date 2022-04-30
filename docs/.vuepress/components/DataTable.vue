<template>
  <table>
    <tr>
      <th v-for="header in headers" :key="header">{{ header }}</th>
    </tr>
    <tr v-for="(row, i) in data" :key="`row-${i}`">
      <td v-for="col in row" :key="col">{{ col }}</td>
    </tr>
  </table>
</template>

<script>
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
export default {
  name: 'DataTable',
  props: ['data'],
  computed: {
    headers(){
      if(!(this.data && this.data.length)) return []
      return Object.keys(this.data[0]).map(value => capitalizeFirstLetter(value))
    }
  }
}
</script>