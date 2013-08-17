# 0.0.3

- Fixed support for `fix-class-names` command. This includes
  various fixes that are aggreated under the GH-28, GH-29, GH-19
  and GH-20. The command is now much more robust and does
  not create false/positives anymore.

- Added `optimize-use <file>` command that will convert all
  relative or absolute usages of namespaces into use statements,
  leaving only the last part at the occuring position.
  (by @pscheit)
