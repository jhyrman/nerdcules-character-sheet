# Introduction #

The `<style>` tag contains supplemental style declarations for this sheet.


# Details #

Every Nerdcules character sheet may have one `<style>` tag. This is an optional tag. It must appear inside the [&lt;head&gt;](TagHead.md) tag, and is seventh in sequence, appearing after the optional [&lt;description&gt;](TagDescription.md) tag.

The `<style>` tag can contain any valid CSS. There are several standard elements and classes used by the Nerdcules parser that you may wish to override:

`form label`

  * Used for all `<label>` elements

`.column`

  * Class used for all columns

`.dot`

  * Class used for all Nerdcules.Dots elements

`.spinner`

  * Class used for all Nerdcules.Spinner elements

`.arrow`

  * Class used for all up- and down-arrows for Dots and Spinners.

## Example ##

```
<sheet>
  <head>
    <style>
      place any valid CSS here
    </style>
  </head>
</sheet>
```