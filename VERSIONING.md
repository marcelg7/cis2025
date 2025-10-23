# Version Management Guide

This application uses **git tags** for version management with a custom calendar versioning scheme.

## Version Format

We use **Calendar Versioning**: `vMAJOR.YEAR.RELEASE`

- **MAJOR** (v4.x.x): Major version of the system
- **YEAR** (vx.2025.x): Year of release
- **RELEASE** (vx.x.1): Release number within that year

**Examples:**
- `v4.2025.1` - Initial production release (2025, release #1)
- `v4.2025.2` - Second release in 2025
- `v4.2026.1` - First release in 2026
- `v5.2026.1` - Major version upgrade in 2026

## How to Create a New Version

### When to Tag

Tag a new version when you're ready to deploy to production:

```bash
# After all commits are pushed to main
git tag v4.2025.2
git push origin v4.2025.2
```

### Versioning Workflow

**Initial Release (2025):**
```bash
git tag v4.2025.1
git push origin v4.2025.1
```

**Next Release (Bug fixes, features, improvements):**
```bash
git tag v4.2025.2
git push origin v4.2025.2
```

**First Release of 2026:**
```bash
git tag v4.2026.1
git push origin v4.2026.1
```

**Major Version Upgrade:**
```bash
git tag v5.2026.1
git push origin v5.2026.1
```

## Deployment Process with Versions

### Development Server (Your Current Machine)

1. Make changes and commit:
   ```bash
   git add .
   git commit -m "Your commit message"
   ```

2. Push to GitHub:
   ```bash
   git push origin main
   ```

3. When ready to deploy, create a version tag:
   ```bash
   git tag v4.2025.2
   git push origin v4.2025.2
   ```

### Production Server

1. Pull the latest changes:
   ```bash
   cd /var/www/mg_apps/cis4
   git pull origin main
   ```

2. Clear caches:
   ```bash
   php artisan optimize:clear
   ```

3. Verify the version in the footer of the website!

## Version Display

The version is automatically displayed in the footer of every page:

- **With tags**: Shows `v4.2025.1`
- **Without tags**: Shows `dev-abc1234` (commit hash)

You can also check the version via command line:

```bash
php artisan tinker --execute="echo app_version();"
```

## Viewing All Tags

```bash
# List all tags
git tag

# List tags with messages
git tag -n

# Show specific tag details
git show v4.2025.1
```

## Deleting a Tag (if needed)

```bash
# Delete local tag
git tag -d v4.2025.2

# Delete remote tag
git push origin --delete v4.2025.2
```

## Best Practices

1. **Tag after testing**: Only tag versions that have been tested
2. **Use annotated tags** (optional but recommended):
   ```bash
   git tag -a v4.2025.2 -m "Release v4.2025.2 - Bug fixes and improvements"
   git push origin v4.2025.2
   ```

3. **Keep a changelog**: Update CHANGELOG.md with each version
4. **Never reuse tags**: If you make a mistake, create a new version
5. **Tag from main branch**: Always tag from the main branch

## Version History

Track your versions in CHANGELOG.md:

```markdown
## v4.2025.2 - 2025-10-25
### Fixed
- Fixed invisible buttons on login page
- Fixed migration ordering issues

## v4.2025.1 - 2025-10-23
### Added
- Initial production release
- Contract management system
- WCOC compliance features
- Security audit and monitoring
```

## Automated Versioning (Future Enhancement)

If you want automatic version bumping in the future, you can set up:
- GitHub Actions to auto-increment on merge to main
- npm version commands
- Conventional commits with semantic-release

For now, manual tagging gives you full control over version numbers.
