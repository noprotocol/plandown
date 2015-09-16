# Plandown

Create JIRA stories using text document.

# Demo

http://plandown.herokuapp.com/

Login in using you username & password (not your email)
Your username can be found on: https://[your-subdomain].atlassian.net/secure/ViewProfile.jspa 

## Example plandown document

```
# General

Write documentation  4h
Setup 30 min


# Frontend

Homepage  3h
Product page  3h
```

# Caveats

Epics are implemented in Jira using customfields, the ids are not the same on every jira installation change the constants in [ImportWizard.php](app/classes/ImportWizard.php) accordingly.

Story points, must be available in the create and edit screens.