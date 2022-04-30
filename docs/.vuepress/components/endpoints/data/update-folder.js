export default {
  body: [
    {
      property: 'name',
      type: 'string',
      description: 'The folder resource name',
    },
    {
      field: 'description',
      type: 'string',
      description: 'Updates the folder\'s description',
    },
    {
      field: 'private',
      type: 'boolean',
      description: 'Updates the folder\'s privacy',
    },
  ],
  response: {
    name: 'Folder',
    route: '/data.html#folder',
  },
}