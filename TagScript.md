# Introduction #

The `<script>` tag enables the inclusion of supplementary JavaScript for the character sheet.


# Details #

Every Nerdcules character sheet may have one `<script>` tag. This is an optional tag, which must appear inside the [&lt;head&gt;](TagHead.md) tag. The `<script>` is eighth in sequence, appearing after the optional [&lt;style&gt;](TagStyle.md) tag.

The `<script>` tag can contain any valid JavaScript.

## Example ##

```
<sheet>
  <head>
    <script>
      place any valid JavaScript here
    </script>
  </head>
</sheet>
```