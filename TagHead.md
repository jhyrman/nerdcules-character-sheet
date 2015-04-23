# Introduction #

The `<head>` tag contains information about this sheet.


# Details #
Every Nerdcules character sheet must have exactly one `<head>` tag, which must be the first tag immediately after the opening `<sheet>` tag.

The `<head>` tag contains the following tags, in order:
  * [&lt;system&gt;](TagSystem.md) - Optional. The gaming system this sheet is for (oWoD; d20; RandK etc.)
  * [&lt;game&gt;](TagGame.md) - Required. This game this sheet is for (Vampire: the Masquerade; Star Wars d20; Legend of the Five Rings; etc.)
  * [&lt;name&gt;](TagName.md) - Required. The name of this sheet (Vampire Neonate; L5R Character; Jedi; etc.)
  * [&lt;copyright&gt;](TagCopyright.md) - Required. The original copyright holder of this sheet (Wizards of the Coast; White Wolf; Alderac; etc.)
  * [&lt;author&gt;](TagAuthor.md) - Required. The person who wrote the Nerdcules XML for this sheet (your name or monicker)
  * [&lt;description&gt;](TagDescription.md) - Optional. A short description of this sheet; used for the meta description, which is referenced by search engines
  * [&lt;style&gt;](TagStyle.md) - Optional. Supplemental CSS definition for this sheet
  * [&lt;script&gt;](TagScript.md) - Optional. Supplemental JavsScript for this sheet

## Example ##
```
<sheet>
  <head>
    <system>Old World of Darkness</system>
    <game>Vampire: the Masquerade 3rd Edition</game>
    <name>Vampire Neonate - Full</name>
    <copyright>White Wolf Publishing</copyright>
    <author>Joshua D. Hyrman</author>
    <description>This is a dynamic, fillable character sheet for a neonate vampire character in the Vampire: the Masquerade 3rd Edition role-playing game</description>
    <style>
      put some CSS in here
    </style>
    <script>
      put some JavaScript in here
    </script>
  </head>
  <set name="example">
  </set>
</sheet>
```