# PHP CRUD Dynamic API

The vision of this project is to empower developers to perform basic CRUD operations effortlessly without the need to write custom PHP code. The scripts are designed to be dynamic, allowing users to interact with the API simply by understanding the provided documentation and making HTTP requests.

## Table of Contents

- [Introduction](#introduction)
- [Connection Configuration](#connection-configuration)
- [Create Data (`create.php`)](#create-data)
- [Read Data (`read.php`)](#read-data)
- [Update Data (`update.php`)](#update-data)
- [Delete Data (`delete.php`)](#delete-data)
- [File Upload (`file-upload.php`)](#file-upload)
- [Customization and Security](#customization-security)
- [Credits, Message from Creator](#credits)

---

## 1. Introduction <a name="introduction"></a>

This documentation provides an overview of a set of PHP scripts designed for basic CRUD operations (Create, Read, Update, Delete) and file uploads. The scripts are intended to be used as an API for managing data in a relational database.

This project provides a set of PHP scripts designed to serve as a simple API for performing CRUD (Create, Read, Update, Delete) operations on a relational database. Additionally, it includes a script for handling file uploads. The scripts are intended to offer a flexible and extensible solution for managing data interactions with a backend server.

---

## 2. Connection Configuration <a name="connection-configuration"></a>

Before using the scripts, ensure that the database connection is properly configured. The connection details can be found in the `configuration/connection.php` file. Modify the file to set your database host, username, password, and database name.

Example `configuration/connection.php`:

```php
define('DB_SERVER', 'YOUR_HOSTNAME');
define('DB_USERNAME', 'YOUR_USERNAME');
define('DB_PASSWORD', 'YOUR_PASSWORD');
define('DB_NAME', 'YOUR_DATABASE');
```

---

## 3. Create Data (create.php) <a name="create-data"></a>

Description:
This script handles the creation of new records in the database based on the provided JSON data.

#### Parameters:

- `table` (required): The name of the database table to insert data into.
- `validation` (optional): Validation rules for the data.
- `data` (required): An array of records to be inserted.

#### Data Validation Rules:

The `validation` is used to validate the data based on the provided rules. The following validation rules are available:

- `required`: The field must not be empty.
- `email`: The field must be a valid email format.
- `numeric`: The field must be numeric.
- `min-length:X`: The field must be at least X characters long.
- `max-length:X`: The field must be at most X characters long.
- `length:X`: The field must be exactly X characters long.
- `unique`: The field value must be unique within the specified table.

#### Example `body` in API request

```json
{
  "table": "users",
  "validation": [
    {
      "name": "required|string",
      "email": "required|email|unique",
      "phone": "required|numeric|unique|length:10",
      "password": "required|min-length:6",
      "age": "optional|numeric"
    }
  ],
  "data": [
    {
      "name": "Imdadullah",
      "email": "imdad@imdos.com",
      "phone": "9992229990",
      "password": "VerySecurePassword",
      "age": 22
    }
  ]
}
```

---

## 4. Read Data (read.php) <a name="read-data"></a>

Description:
This script retrieves data from the database based on the specified parameters.

#### Parameters:

- `table` (required): The name of the database table to query.
- `select` (required): An array of columns to select. Default is all columns (\*).
- `join` (optional): An array of join clauses for performing joins.
- `conditions` (optional): An array of conditions with object with `on`, `type` and `value` for filtering data.
- `rawConditions` (optional): An array of raw conditions for filtering data, make you sure pass either `conditions` or `rawConditions`.
- `order` (optional): An object with `on` and `type` for ordering data.
- `limit` (optional): Limit the number of records returned.

#### Example `body` in API request

```json
{
  "table": "users",
  "select": ["id", "name", "email", "age"],
  "order": { "on": "id", "type": "DESC" },
  "conditions": [
    {
      "on": "age",
      "type": ">=",
      "value": "18"
    },
    {
      "on": "status",
      "type": "=",
      "value": "active"
    },
    {
      "on": "email",
      "type": "LIKE",
      "value": "@gmail.com%"
    }
  ],
  "limit": 10
}
```

#### Example `body` with `rawConditions` in API request

```json
{
  "table": "users",
  "select": ["id", "name", "email", "age"],
  "order": { "on": "id", "type": "DESC" },
  "rawConditions": [
    "WHERE age >= '18' OR type = 'customer' AND status = 'active'"
  ]
}
```

#### Example `body` with `JOIN` Parameter

Note: You'll have to mention each table name along with the column name to use `JOIN`

```json
{
  "table": "users",
  "select": [
    "users.name",
    "users.email",
    "items.title",
    "items.price",
    "purchases.amount",
    "purchases.created_at AS purchased_date"
  ],
  "conditions": [
    {
      "on": "purchases.item_id",
      "type": "=",
      "value": "102"
    }
  ],
  "join": [
    {
      "table": "purchases",
      "on": ["purchases.user_id", "users.id"],
      "type": "LEFT"
    },
    {
      "table": "items",
      "on": ["items.id", "purchases.item_id"],
      "type": "LEFT"
    }
  ]
}
```

---

## 5. Update Data (update.php) <a name="update-data"></a>

Description:
This script updates existing records in the database based on the specified parameters.

#### Parameters:

- `table` (required): The name of the database table to query.
- `data` (required): An array of fields and values to be updated.
- `conditions` (required): An array of conditions for identifying records to update.
- `validation` (optional): Validation rules for the data.

#### Example `body` in API request

```json
{
  "table": "users",
  "data": [
    {
      "name": "Imdadullah Babu",
      "age": 22
    }
  ],
  "validation": [
    {
      "name": "required|string",
      "age": "optional|numeric"
    }
  ],
  "conditions": [
    {
      "on": "id",
      "type": "=",
      "value": "1"
    }
  ]
}
```

---

## 6. Delete Data (delete.php) <a name="delete-data"></a>

Description:
This script deletes records from the database based on the specified parameters.

#### Parameters:

- `table` (required): The name of the database table to delete from.
- `conditions` (required): An array of conditions for identifying records to delete.

#### Example `body` in API request

```json
{
  "table": "users",
  "conditions": [
    {
      "on": "id",
      "type": "=",
      "value": "1"
    }
  ]
}
```

---

## 7. File Upload (file-upload.php) <a name="file-upload"></a>

Description:
This script handles the uploading of files to a specified destination.

#### Parameters:

- `fileDestination` (required): The directory where the files will be stored.
- `fileValidation` (required): A comma-separated list of allowed file extensions.

Note: You can upload multiple files at once, and this will not insert into your database table, You'll get the url as response and you can save it to the database.

#### Example API request

```javascript
<script>
    const uploadButton = document.getElementById("button");

    uploadButton.addEventListener("click", async function () {
      const fileInput = document.getElementById("file");
      const imageInput = document.getElementById("image");

      const file = fileInput.files[0];
      const image = imageInput.files[0];

      const formData = new FormData();
      formData.append("file", file);
      formData.append("image", image);
      formData.append("fileDestination", "Files"); // Files will uploaded to the mentioned destination that is 'Files'
      formData.append("fileValidation", ["jpg", "png", "pdf"]);

      const request = await fetch("YOUR_API_REQUEST_ENDPOINT", {
          method: "POST",
          body: formData,
      });

      const response = await request.json();
      console.log(response);
    });
</script>
```

#### API Response

```
{
    "file": "Files/2024011984368.jpg",
    "image": "Files/2024011960039.jpg"
}
```

Note: Customize and extend the scripts based on your project's requirements. Ensure proper validation and security measures are implemented in a production environment.

---

## 8. Customization and Security <a name="customization-security"></a>

For additional security measures or custom logic, developers can extend the functionality by adding their own logic to the `configuration/custom-functions.php` file. This provides a space to incorporate token validation, custom authentication, or any other security measures according to project requirements.

### Instructions for Customization:

- Open the configuration/custom-functions.php file.
- Add your own custom logic, functions, or security measures.
- Use the added functions within the codebase as needed.

### Notes:

- Developers are encouraged to review and customize the provided scripts to meet the specific requirements of their projects.
- Custom functions added to custom-functions.php can be seamlessly integrated into the existing codebase.
- Developers have the freedom to enhance security and implement project-specific logic according to their needs.

---

## 9. Credits: <a name="credits"></a>

This project was developed by [Imdadullah Babu](https://imdos.in), The scripts aim to provide a foundation for PHP developers to integrate basic database operations and file uploads into their projects without writing repeatable codes.

#### Acknowledgments

This project is open-source, and we welcome contributions from developers worldwide. Whether you're interested in adding new features, improving documentation, fixing bugs, or suggesting enhancements, your contributions are valuable.

#### Message from Creator

Together, we can make this project even more versatile and beneficial for the developer community. Your contributions, big or small, are highly appreciated.

Thank you for being a part of our open-source journey!
