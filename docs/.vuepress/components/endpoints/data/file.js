export default [
  {
    field: 'created_at',
    type: 'string (ISO 8601)',
    description: 'The time at which the file was created',
  },
  {
    field: 'updated_at',
    type: 'string (ISO 8601)',
    description: 'The last time the file was updated',
  },
  {
    field: 'id',
    type: 'string',
    description: 'The file ID',
  },
  {
    field: 'name',
    type: 'string',
    description: 'The file\'s resource name',
  },
  {
    field: 'description',
    type: 'string',
    description: 'The file\'s resource description',
  },
  {
    field: 'type',
    type: 'string',
    description: 'The file type. E.g. image, docs, etc.',
  },
  {
    field: 'image',
    type: 'object',
    description: 'Image links of the file. Empty for non-image files with no thumbnail generated',
  },
  {
    field: 'extension',
    type: 'string',
    description: 'The file extension',
  },
  {
    field: 'private',
    type: 'boolean',
    description: 'The file privacy. Maybe inherited from its folder',
  },
  {
    field: 'disk',
    type: 'string',
    description: 'The file stroage disk',
  },
  {
    field: 'size',
    type: 'number',
    description: 'The file size, in bytes',
  },
  {
    field: 'location',
    type: 'string',
    description: 'The file location. This value is reflective of the file\'s absolute path',
  },
]