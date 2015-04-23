# Structure Tags #
  * [&lt;sheet&gt;](TagSheet.md) - Required. The root element for a character sheet
  * [&lt;head&gt;](TagHead.md) - Required. Holds information about this sheet
  * [&lt;system&gt;](TagSystem.md) - Optional. The gaming system this sheet is for (oWoD; d20; RandKl etc.)
  * [&lt;game&gt;](TagGame.md) - Required. This game this sheet is for (Vampire: the Masquerade; Star Wars d20; Legend of the Five Rings; etc.)
  * [&lt;name&gt;](TagName.md) - Required. The name of this sheet (Vampire Neonate; L5R Character; Jedi; etc.)
  * [&lt;copyright&gt;](TagCopyright.md) - Required. The original copyright holder of this sheet (Wizards of the Coast; White Wolf; Alderac; etc.)
  * [&lt;author&gt;](TagAuthor.md) - Required. The person who wrote the Nerdcules XML for this sheet (your name or monicker)
  * [&lt;description&gt;](TagDescription.md) - Optional. A short description of this sheet; used for the meta description, which is referenced by search engines
  * [&lt;style&gt;](TagStyle.md) - Optional. Supplemental CSS definition for this sheet
  * [&lt;script&gt;](TagScript.md) - Optional. Supplemental script for this sheet
  * [&lt;set&gt;](TagSet.md) - Required. Defines a set of attributes; a sheet must have at least one set
  * [&lt;column&gt;](TagColumn.md) - Required. Defines a column; a set must have at least one column
  * [&lt;break /&gt;](TagBreak.md) - Defines a printable page break
# Field Tags #
Field tags can only appear inside of a column tag.
  * [&lt;text&gt;](TagText.md) - Defines a text field
  * [&lt;select&gt;](TagSelect.md) - Defines a select field
  * [&lt;option&gt;](TagOption.md) - Defines an option within a select field
  * [&lt;dot&gt;](TagDot.md) - Defines a Nerdcules.Dots element
  * [&lt;spinner&gt;](TagSpinner.md) - Defines a Nerdcules.Spinner element
  * [&lt;em&gt;](TagEm.md) - Defines a title/heading
  * [&lt;check&gt;](TagCheck.md) - Defines a checkbox field
  * [&lt;textarea&gt;](TagTextarea.md) - Defines a textarea field
  * [&lt;blank&gt;](TagBlank.md) - Defines a blank line