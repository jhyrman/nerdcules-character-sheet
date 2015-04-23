This is a simple parser that reads in an XML character sheet definition, and generates a useable HTML character sheet.
It features the "Nerdcules.Dots" widget, which creates a changeable set of circles (dots) to represent dice pools for certain games; and the "Nerdcules.Spinner" widget, which creates a number that can be increased and decreased.
It also features the "Nerdcules.CharacterSheet", allowing the sheet to be loaded from or saved as a JSON object for easy database integration.

### Version 1.2 ###
Version 1.2 of the SheetParser is here! Features support for tables with the `<table>`, `<tr>`, and `<td>` tags, plus the new `<repeat>` tag! The table feature allows rendered sheets to be much more navigable, and the `<repeat>` tag allows authors to create more compact XML sheets.