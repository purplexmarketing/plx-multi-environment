# Contributing to PLX Projects

The following is a set of guidelines for contributing to PLX Projects, which are hosted on the Purplex Marketing Gitlab system (http://gitlab.plx.mk).
These are just guidelines, not rules, use your best judgment and feel free to propose changes to this document in a pull request.

### Code of Conduct
All contributors are expected to adhere to the guidelines created by the PLX Development Team which can be found at http://plx.mk/kb/guidelines.
By participating, you are expected to uphold the principals of these guidelines.
Please report unacceptable behavior to [matt@plx.mk](mailto:matt@plx.mk).

## How Can I Contribute?

### Reporting Bugs

#### Public Contributors

All bugs and issues should be reported using the issues section on this Gitlab project. Please ensure you use the bug reporting template below and provide as much information as possible:

    [Short description of problem here]

    **Reproduction Steps:**

    1. [First Step]
    2. [Second Step]
    3. [Other Steps...]

    **Expected behavior:**

    [Describe expected behavior here]

    **Observed behavior:**

    [Describe observed behavior here]

    **Screenshots and GIFs**

    ![Screenshots and GIFs which follow reproduction steps to demonstrate the problem](url)

    **Plugin version:** [Enter the plugin version here]
    **OS and version:** [Enter OS name and version here]
    **Browser and version:** [Enter Browser name and version here]

    **Additional information:**

    * Problem started happening recently, didn't happen in an older version of plugin: [Yes/No]
    * Problem can be reliably reproduced, doesn't happen randomly: [Yes/No]

#### PLX Team Contributors

All bugs and issues should be reported using the Bugs system on the company Portal system at http://portal.ascotgroup.co.uk/bugs/home/

## Important Notes

### Incrementing the project version number

Each time a series of changes are made to the project the version number should be increased to reflect these changes. The guide below shows which number to increment depending on what has changed in the project:

* MAJOR version when you make incompatible API changes,
* MINOR version when you add functionality in a backwards-compatible manner, and
* PATCH version when you make backwards-compatible bug fixes.

Our projects should conform to the standards set down at http://semver.org for version numbers

### Updating the changelog

Each time a new version of the project is released the changelog needs to be updated with a summary of changes that have been made added under a new version number heading at the top of the file.

The following tags should be used to prefix each change made within the new version:

* [Added] for new features.
* [Changed] for changes in existing functionality.
* [Deprecated] for once-stable features removed in upcoming releases.
* [Removed] for deprecated features removed in this release.
* [Fixed] for any bug fixes.
* [Security] to invite users to upgrade in case of vulnerabilities.

For more information on maintaining the changelog see http://keepachangelog.com