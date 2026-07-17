import os

path = r"C:\laragon\www\silab-keperawatan\resources\views\dashboard-laboran.blade.php"
content = open(path, "rb").read()

# Replace for 'alat'
old_alat1 = b'title="Unduh Rekap Excel (MS Excel Compatible)"\r\n                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"'
new_alat1 = b'title="Unduh Rekap Excel (MS Excel Compatible)">\r\n                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"'

old_alat2 = b'title="Unduh Rekap Excel (MS Excel Compatible)"\n                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"'
new_alat2 = b'title="Unduh Rekap Excel (MS Excel Compatible)">\n                                        <svg class="-ml-1 mr-2 h-4 w-4 text-emerald-600"'

content = content.replace(old_alat1, new_alat1)
content = content.replace(old_alat2, new_alat2)

open(path, "wb").write(content)
print("Fix Excel Icon: Done!")
