# Release Process

This document outlines the release process for Pandora FMS Open Source.

## Automated Releases

Releases are created automatically by a GitHub Actions workflow. The process is triggered when a new tag is pushed to the repository.

### Tagging Convention

To trigger a new release, create and push a new **annotated tag** following semantic versioning. Using the `v` prefix (e.g., `vX.Y.Z`) is highly recommended for release tags.

```bash
# Example for version 1.2.3 (use -a for an annotated tag)
git tag -a v1.2.3 -m "Release v1.2.3"
git push --follow-tags
```

The workflow will automatically detect the new tag and trigger the release process.

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
