# Task API Documentation

## Response Codes
- `200` OK
- `201` Created
- `204` No content
- `3xx` Redirection
- `400` Bad Request
- `401` not authenticated (send to login screen)
- `403` not authorized (role does not permit request)
- `404` route/model not found
- `419` CSRF mismatch (session is invalid, reload page or GET `/sanctum/csrf-cookie`)
- `422` Validation error
- `500` Internal Server Error

## Task

### Create Tasks
`POST /api/v1/lists/{list}`
<details>
<summary>Request</summary>
<p>

```json
{
    "name": "Take out garbage"
}
```

</p>
</details>
<details>
<summary>Response</summary>
<p>

```json
{
    "id": 1,
    "name": "Take out garbage",
    "list_id": 2,
    "complete": false,
    "created_at": "2018-01-30T00:00:00.000000Z",
    "updated_at": "2018-01-30T00:00:00.000000Z"
}
```

</p>
</details>

### Update Tasks
`PATCH /api/v1/lists/{list}/{task}`
<details>
<summary>Request</summary>
<p>

```json
    {
        "name": "Take wife out to dinner",
        "complete": true
    }
```
</p>
</details>
<details>
<summary>Response</summary>
<p>

```json
{
    "id": 4,
    "name": "Take wife out to dinner",
    "list_id": 3,
    "complete": true,
    "created_at": "2018-01-30T00:00:00.000000Z",
    "updated_at": "2018-01-30T00:00:00.000000Z"
}
```

</p>
</details>

### Delete Tasks
`DELETE /api/v1/lists/{list}/{task}`
 
 Response `204`