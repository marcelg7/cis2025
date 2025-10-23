# Version Management Guide

This application uses **git tags** for version management with semantic versioning.

## Version Format

We use **Semantic Versioning (SemVer)**: `vMAJOR.MINOR.PATCH`

- **MAJOR** (v2.0.0): Breaking changes, major features
- **MINOR** (v1.1.0): New features, backward compatible
- **PATCH** (v1.0.1): Bug fixes, small improvements

## How to Create a New Version

### When to Tag

Tag a new version when you're ready to deploy to production:

```bash
# After all commits are pushed to main
git tag v1.0.0
git push origin v1.0.0
```

### Versioning Workflow

**Initial Release:**
```bash
git tag v1.0.0
git push origin v1.0.0
```

**Bug Fix (Patch):**
```bash
git tag v1.0.1
git push origin v1.0.1
```

**New Feature (Minor):**
```bash
git tag v1.1.0
git push origin v1.1.0
```

**Breaking Change (Major):**
```bash
git tag v2.0.0
git push origin v2.0.0
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
   git tag v1.0.1
   git push origin v1.0.1
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

- **With tags**: Shows `v1.0.0`
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
git show v1.0.0
```

## Deleting a Tag (if needed)

```bash
# Delete local tag
git tag -d v1.0.0

# Delete remote tag
git push origin --delete v1.0.0
```

## Best Practices

1. **Tag after testing**: Only tag versions that have been tested
2. **Use annotated tags** (optional but recommended):
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0 - Initial production release"
   git push origin v1.0.0
   ```

3. **Keep a changelog**: Update CHANGELOG.md with each version
4. **Never reuse tags**: If you make a mistake, create a new version
5. **Tag from main branch**: Always tag from the main branch

## Version History

Track your versions in CHANGELOG.md:

```markdown
## v1.0.1 - 2025-10-23
### Fixed
- Fixed invisible buttons on login page
- Fixed migration ordering issues

## v1.0.0 - 2025-10-23
### Added
- Initial production release
- Contract management system
- WCOC compliance features
```

## Automated Versioning (Future Enhancement)

If you want automatic version bumping in the future, you can set up:
- GitHub Actions to auto-increment on merge to main
- npm version commands
- Conventional commits with semantic-release

For now, manual tagging gives you full control over version numbers.
