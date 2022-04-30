export default [
  {
    field: 'created_at',
    type: 'string (ISO 8601)',
    description: 'The time at which the folder was created',
  },
  {
    field: 'updated_at',
    type: 'string (ISO 8601)',
    description: 'The last time the folder was updated',
  },
  {
    field: 'id',
    type: 'string',
    description: 'The folder ID',
  },
  {
    field: 'name',
    type: 'string',
    description: 'The folder resource name',
  },
  {
    field: 'description',
    type: 'string',
    description: 'Resource description',
  },
  {
    field: 'type',
    type: 'string',
    description: 'Returns `folder`',
  },
  {
    field: 'private',
    type: 'boolean',
    description: 'The folder privacy. Maybe inherited from its parent folder',
  },
  {
    field: 'disk',
    type: 'string',
    description: 'The folders stroage disk',
  },
  {
    field: 'size',
    type: 'number',
    description: 'The created file size, in bytes',
  },
  {
    field: 'location',
    type: 'string',
    description: 'The folder location. This value is reflective of the folder\'s absolute path',
  },
  {
    field: 'folders',
    type: 'array',
    description: 'Child folders',
  },
  {
    field: 'files',
    type: 'array',
    description: 'The files in the folder',
  },
]
