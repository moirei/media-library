export default {
  body: [
    {
      property: "file",
      type: "UploadedFile",
      required: "true",
      description: "The file to be uploaded",
    },
    {
      property: "location",
      type: "string",
      required: "false",
      description: `
        The location in which the file will be saved. A folder will be created if it does\t exist.
        If your folder already exists, it's preferred to provide its ID. If using a path name, make sure the location does not include the package's storage path nor your workspace.
      `,
    },
    {
      property: "name",
      type: "string",
      required: "false",
      description: "Name the uploaded file",
    },
    {
      property: "description",
      type: "string",
      required: "false",
      description: "Set a description for the uploaded file",
    },
    {
      property: "private",
      type: "boolean",
      required: "false",
      description: "Set the privacy for the uploaded file",
    },
  ],
  response: {
    name: "File",
    route: "/data.html#file",
  },
};
