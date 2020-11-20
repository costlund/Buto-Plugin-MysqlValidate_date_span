# Buto-Plugin-MysqlValidate_date_span
Validate overlapping dates in db.

## Settings

Database settings in theme /config/settings.yml.
```
plugin:
  mysql:
    validate_date_span:
      enabled: true
      data:
        mysql: 'yml:/../buto_data/mysql.yml'
```

## Usage

Form settings example 1.
```
items:
  id:
    type: hidden
    label: id
    default: rs:id
  parent_id:
    type: hidden
    label: parent_id
    mandatory: true
    default: rs:parent_id
  date_from:
    type: date  
    label: Date from
    mandatory: false
    default: rs:date_from
    validator:
      -
        plugin: mysql/validate_date_span
        method: validate_date_span
        data:
          db:
            table_name: child
            date1: date_from
            date2: date_to
            key_fields:
              - 
                db: parent_id
                get: parent_id
          items:
            date1: date_from
            date2: date_to
  date_to:
    type: date  
    label: Date to
    default: rs:date_to
```

Form settings example 2.
Param clean_up_key has default value of id. If id of table in request is not the same one could set this.
```
items:
  account_id:
    type: hidden
    label: account_id
    default: get:account_id
  id:
    type: hidden
    label: id
    default: get:id
  date_from:
    type: date  
    label: Date from
    mandatory: false
    default: rs:date_from
    validator:
      -
        plugin: mysql/validate_date_span
        method: validate_date_span
        data:
          db:
            table_name: child_toys
            date1: date_from
            date2: date_to
            key_fields:
              - 
                db: child_id
                get: id
              - 
                db: part
                get: part
          items:
            date1: date_from
            date2: date_to
          clean_up_key: account_id
  date_to:
    type: date  
    label: Date to
    default: rs:date_to
```
