export default {
  body: [
    {
      property: "location",
      type: "string",
      description: "The location to browse",
    },
    {
      property: "filesOnly",
      type: "boolean",
      description: "Only include files in results",
    },
    {
      property: "type",
      type: "string",
      description: "File types to include",
    },
    {
      property: "mime",
      type: "string",
      description: "File mime types to include",
    },
    {
      property: "private",
      type: "boolean",
      description: "Include files or folders with privacy true or false.",
    },
  ],
  response: {
    name: "array<File|Folder>",
    route: "/data.html",
  },
};
