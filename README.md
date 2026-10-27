# MultiFlexi NetBox credential prototype

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
![Packaging: deb](https://img.shields.io/badge/packaging-.deb-red?logo=debian&logoColor=white)

Provides the **NetBox** credential prototype for
[MultiFlexi](https://multiflexi.eu/): `NETBOX_URL` + `NETBOX_TOKEN`.

## Packages

| Deb | Contents |
|-----|----------|
| `multiflexi-netbox` | `MultiFlexi\CredentialProtoType\NetBox` + icon |
| `multiflexi-netbox-ui` | Web UI helper that probes `/api/status/` |

## Fields

| Keyword | Type | Purpose |
|---------|------|---------|
| `NETBOX_URL` | url | NetBox base URL (e.g. `https://netbox-dev.spojenet.cz/`) |
| `NETBOX_TOKEN` | password | API token (`Authorization: Token …`) |

`checkAvailability()` calls `GET {NETBOX_URL}/api/status/`.

## Usage

```bash
sudo apt install multiflexi-netbox multiflexi-netbox-ui
# postinst runs: multiflexi-cli credential-prototype:sync
```

Create a company credential of type **NetBox**, then assign it to runtemplates
whose apps list `"NetBox"` in `requirements` (e.g. isp-tools `blocknet` /
`unblocknet`).

## Development

```bash
composer install
vendor/bin/phpunit
dpkg-buildpackage -us -uc -b
```
