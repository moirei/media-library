export default {
  body: [
    {
      property: "file",
      type: "UploadedFile",
      required: "true",
      description: "The attachment file to be uploaded",
    },
  ],
  response: {
    name: "Attachment",
    route: "/data.html#attachment",
  },
};
