# Compatibility

Tested against WordPress 6.0–7.1 on PHP 7.4, 8.1, 8.2 and 8.3 with MariaDB 10.11.

| area | notes |
|---|---|
| Page builders | keep their minifier off, this plugin's on |
| Object cache | detected; the plugin leaves transients to the drop-in |
| Multisite | per-site settings and per-site cache directories |
| Security plugins | admin screens are capability-checked and nonce-protected |
