export default {
  body: [
    {
      property: 'name',
      type: 'string',
      description: 'The folder resource name',
    },
    {
      property: 'location',
      type: 'string',
      description: `
        The location folder ID or path to place the folder. If a path is provided, parent folders may be created if they dont exists.
        If creating a subfolder, it's preferred to provide its ID. Otherwise make sure the location does not include the package's storage path nor your workspace.
      `,
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
