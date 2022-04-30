export default {
  body: [
    {
      property: 'name',
      type: 'string',
      description: 'The file resource name; not the filename',
    },
    {
      field: 'description',
      type: 'string',
      description: 'Updates the file\'s description',
    },
    {
      field: 'private',
      type: 'boolean',
      description: 'Updates the file\'s privacy',
    },
  ],
  response: {
    name: 'File',
    route: '/data.html#file',
  },
}