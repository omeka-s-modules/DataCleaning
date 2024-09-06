# DataCleaning

An [Omeka S](https://omeka.org/s/) module for low-level auditing and cleaning of resource metadata. It is designed to prepare resource metadata for use in visualizations.

Changes made by Data Cleaning cannot be easily undone, and can be destructive. Due to the powerful nature of the module, it can only be used by Global Administrators.

Note: Before running any audit, make sure you have a recent backup of your data. Backing up your SQL database can be done with many hosts, and you can back up your entire Omeka installation if you installed through an app manager.

This is a sample workflow for a user who wants to audit the titles of items in a specific item set:

- In the left-hand navigation, go to the Data Cleaning module.
- Click on "Prepare new audit" button (top right).
- Select criteria (expand arrows for info):
  - Resource type: "Item"
  - Resource query: item_set_id[]=1234
  - Property: "Dublin Core: Title"
  - Audit column: "value"
  - Data type: "Text".
- Ignore the "Advanced" section.
- Click "Submit" (top right).
- Audit the "From: value" column for potential corrections or removals.
- Make corrections by entering the correct text into the "To: value" column.
- Make removals by checking the "Remove" checkbox.
- Click "Submit" (top right).
- Click to confirm the submission.
- Refresh the "Past audits" page until the job is marked as "Completed".
- Check the items to see if the values were corrected and removed.



# Copyright

DataCleaning is Copyright © 2020-present Corporation for Digital Scholarship, Vienna, Virginia, USA http://digitalscholar.org

The Corporation for Digital Scholarship distributes the Omeka source code
under the GNU General Public License, version 3 (GPLv3). The full text
of this license is given in the license file.

The Omeka name is a registered trademark of the Corporation for Digital Scholarship.

Third-party copyright in this distribution is noted where applicable.

All rights not expressly granted are reserved.

