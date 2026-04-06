# Release Process

This document outlines the release process for Pandora FMS Open Source.

## Automated Releases

Releases are created automatically by a GitHub Actions workflow. The process is triggered when a new tag is pushed to the repository.

### Tagging Convention

To trigger a new release, create and push a new tag following semantic versioning. Both `vX.Y.Z` and `X.Y.Z` formats are supported.

```bash
# Example for version 1.2.3
git tag 1.2.3
git push origin 1.2.3
```

or

```bash
git tag v1.2.3
git push origin v1.2.3
```

The workflow will automatically strip the `v` prefix to create a standardized release version (e.g., `1.2.3`).

### Release Artifacts

The automated release process will generate the following artifacts and attach them to the GitHub Release:

- `pandoraopen-console-X.Y.Z.tar.gz`
- `pandoraopen-server-X.Y.Z.tar.gz`
- `pandoraopen-agent-X.Y.Z.tar.gz`
- `PandoraOpenAgent_Setup-X.Y.Z.exe`

### Manual Intervention

If a release fails, you can manually intervene:

1.  **Delete the release** from the GitHub Releases page.
2.  **Delete the tag locally**: `git tag -d <tag_name>`
3.  **Delete the tag on the remote**: `git push --delete origin <tag_name>`
4.  After fixing the issue, you can re-run the process by creating and pushing the tag again.
