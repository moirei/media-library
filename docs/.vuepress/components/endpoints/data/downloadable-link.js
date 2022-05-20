export default {
  body: [
    {
      property: "ttl",
      type: "integer",
      description:
        "In seconds. For private files; the ttl from the time of request the url is valid for",
    },
  ],
  response: [
    {
      field: "url",
      type: "string",
      description: "A url to download the file",
    },
    {
      field: "filename",
      type: "string",
      description: "Filename of the download",
    },
    {
      field: "mimetype",
      type: "string",
      description: "Mimetype of the download",
    },
  ],
};
