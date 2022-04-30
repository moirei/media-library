export default {
  body: [
    {
      property: 'location',
      type: 'string',
      description: `
        The location to move the file to. The folder ID or path.
        It's preferred to provide IDs, otherwise make sure the location does not include the package's storage path nor your workspace.
      `,
    },
  ],
  response: {
    name: 'File',
    route: '/data.html#file',
  },
}