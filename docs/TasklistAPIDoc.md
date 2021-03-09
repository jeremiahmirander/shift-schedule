# Tasklist API Documentation

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

## Tasklist

### Index Lists
`GET /api/v1/lists`
<details>
<summary>Response</summary>
<p>

```json
[
	{
		"id": 1,
		"created_at": "2019-02-30T00:00:00.000000Z",
		"updated_at": "2020-11-29T00:00:00.000000Z",
		"name": "chores",
		"user_id": 2   
	}
	{
		"id": 2,
		"created_at": "2020-02-19T00:00:00.000000Z",
		"updated_at": "2020-04-11T00:00:00.000000Z",
		"name": "work",
		"user_id": 2   
	}
	{
		"id": 3,
		"created_at": "2020-02-03T00:00:00.000000Z",
		"updated_at": "2020-03-19T00:00:00.000000Z",
		"name": "meetings",
		"user_id": 2   
	}
	
]
```

</p>
</details>

### Create Tasklist
`POST /api/v1/lists`
<details>
<summary>Request</summary>
<p>

```json
    {
        "name": "monday report"
    }
```
</p>
</details>

<details>
<summary>Response</summary>
<p>

```json
{
    "id": 9,
    "name": "monday report"
}
```

</p>
</details>

### Show Tasklist
`GET /api/v1/lists/{list}`

<details>
<summary>Response</summary>
<p>

```json
[
	{
		"id": 1,
		"name": "preparation",
		"completed": true,
		"list_id": 3,
		"created_at": "2020-01-19T00:00:00.000000Z",
		"updated_at": "2020-01-19T00:00:00.000000Z",
	}
	{
		"id": 2,
		"name": "standup",
		"completed": true,
		"list_id": 3,
		"created_at": "2020-01-19T00:00:00.000000Z",
		"updated_at": "2020-01-20T00:00:00.000000Z",
	}
	{
		"id": 3,
		"name": "breakout",
		"completed": false,
		"list_id": 3,
		"created_at": "2020-01-19T00:00:00.000000Z",
		"updated_at": "2020-01-20T00:00:00.000000Z",
	}
	{
		"id": 4,
		"name": "report",
		"completed": false,
		"list_id": 3,
		"created_at": "2020-01-19T00:00:00.000000Z",
		"updated_at": "2020-01-20T00:00:00.000000Z",
	}
]
```

</p>
</details>

### Update Tasklist
`PATCH /api/v1/lists/{list}`
<details>
<summary>Request</summary>
<p>

```json
    {
        "name": "chores-updated",
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
		"created_at": "2019-02-30T00:00:00.000000Z",
		"updated_at": "2020-11-29T00:00:00.000000Z",
		"name": "chores-updated",
		"user_id": 2   
	}
```

</p>
</details>

### Delete Tasklist
`DELETE /api/v1/lists/{list}`