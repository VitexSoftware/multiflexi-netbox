# AGENTS.md — multiflexi-netbox

## Purpose

MultiFlexi credential prototype for NetBox REST API (`NETBOXURL`, `NETBOXTOKEN`).

## Layout

- `src/MultiFlexi/CredentialProtoType/NetBox.php` → `/usr/share/php/MultiFlexi/CredentialProtoType/`
- `src/MultiFlexi/Ui/CredentialType/NetBox.php` → `/usr/lib/multiflexi/MultiFlexi/Ui/CredentialType/`
- `multiflexi/netbox.credprototype.json` — reference only (sync discovers PHP class)
- `debian/multiflexi-netbox.postinst` — `multiflexi-cli credential-prototype:sync`

## Consumers

- `isp-tools` NetBoxer (`Ease\Shared::cfg('NETBOXURL'|'NETBOXTOKEN')`)
- Any MultiFlexi app with `"requirements": ["NetBox"]`

## Do not

- Do not run `composer` in postinst
- Do not rename field keywords (`NETBOXURL` / `NETBOXTOKEN` match isp-tools)
