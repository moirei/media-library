export default {
  body: [
    {
      property: 'name',
      type: 'string',
      description: 'The name of the resource. Defaults to the file or folder name',
    },
    {
      property: 'description',
      type: 'string',
      description: 'The description of the resource',
    },
    {
      property: 'public',
      type: 'boolean',
      description: 'Whether the shared content is public',
    },
    {
      property: 'access_emails',
      type: 'array',
      description: 'If not public, a list of emails that may authenticate to access content',
    },
    {
      property: 'access_keys',
      type: 'array',
      description: 'Required if not public. A list of access codes. Each code must be at least 6-digits',
    },
    {
      property: 'access_type',
      type: 'enum',
      description: 'Indicate if the access is a token or secret. Values: `token`, `secret`. The default is `token`',
    },
    {
      property: 'expires_at',
      type: 'string (ISO 8601)',
      description: 'Sets an expiry on the resource',
    },
    {
      property: 'can_remove',
      type: 'boolean',
      description: 'Indicate whether shared files/folders may be deleted',
    },
    {
      property: 'can_upload',
      type: 'boolean',
      description: 'If sharing a folder, whether files may be uploaded',
    },
    {
      property: 'can_upload',
      type: 'boolean',
      description: 'If sharing a folder, whether files may be uploaded',
    },
    {
      field: 'max_downloads',
      type: 'integer',
      description: 'Limit max downloads',
    },
    {
      field: 'allowed_upload_types',
      type: 'array',
      description: 'Limit uploadable mime types',
    },
  ],
  response: [
    {
      field: 'id',
      type: 'string',
      description: 'The shared content resource ID',
    },
    {
      field: 'url',
      type: 'string',
      description: 'A url to share with external',
    },
  ],
}
