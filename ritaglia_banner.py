"""
Ritaglia e ridimensiona il banner a 1500x375px (rapporto 4:1)
preservando sia il contenuto principale che la barra inferiore.

Uso: python ritaglia_banner.py <percorso_immagine>
Esempio: python ritaglia_banner.py banner_originale.png
"""

import sys
from pathlib import Path
from PIL import Image


def ritaglia_banner(src_path: str) -> None:
    img = Image.open(src_path).convert("RGB")
    orig_w, orig_h = img.size
    print(f"Immagine originale: {orig_w}x{orig_h}px")

    TARGET_W, TARGET_H = 1500, 375

    # --- Step 1: scala a 1500px di larghezza ---
    scale   = TARGET_W / orig_w
    scaled_h = int(orig_h * scale)
    scaled  = img.resize((TARGET_W, scaled_h), Image.LANCZOS)
    print(f"Scalata proporzionalmente a: {TARGET_W}x{scaled_h}px")

    if scaled_h <= TARGET_H:
        # Immagine già abbastanza bassa: scala diretta
        result = scaled.resize((TARGET_W, TARGET_H), Image.LANCZOS)
    else:
        # Stima altezza barra inferiore (tipicamente ~19% dell'immagine)
        bar_pct  = 0.19
        bar_h    = int(scaled_h * bar_pct)
        main_h   = TARGET_H - bar_h

        print(f"Barra inferiore stimata: {bar_h}px | Contenuto principale: {main_h}px")

        top_crop    = scaled.crop((0, 0,               TARGET_W, main_h))
        bottom_crop = scaled.crop((0, scaled_h - bar_h, TARGET_W, scaled_h))

        result = Image.new("RGB", (TARGET_W, TARGET_H))
        result.paste(top_crop,    (0, 0))
        result.paste(bottom_crop, (0, main_h))

    out_path = Path(src_path).with_stem(Path(src_path).stem + "_1500x375")
    out_path = out_path.with_suffix(".jpg")
    result.save(str(out_path), "JPEG", quality=94, optimize=True)
    print(f"\n✓ Salvato: {out_path}  ({result.size[0]}x{result.size[1]}px)")


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Uso: python ritaglia_banner.py <percorso_immagine>")
        sys.exit(1)
    ritaglia_banner(sys.argv[1])
