# proca-civicrm

This extension allows to synchronise [proca](https://proca.app) (the most privacy friendly campaigning tool) and civicrm.


for performance reasons It does it in two steps: 

- fetch the data from proca and queue them, 
- process them

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.2+
* CiviCRM

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).


## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/fixthestatusquo/proca-civicrm.git
cv en proca
```

## Getting Started

The configuration screen is under civicrm/admin/setting/proca


## Known Issues

We test for duplicates of contacts, we don't test for duplicates of activities
