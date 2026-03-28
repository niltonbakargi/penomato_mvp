/**
 * exif_gps.js — Extrator de GPS do EXIF de imagens JPEG
 * Lê os bytes binários do arquivo diretamente, sem dependências externas.
 * Retorna Promise<{lat, lng}> ou Promise<null> se não encontrar.
 */

async function lerGpsExif(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload  = (e) => resolve(_parseGps(e.target.result));
        reader.onerror = ()  => resolve(null);
        reader.readAsArrayBuffer(file);
    });
}

function _parseGps(buffer) {
    try {
        const view = new DataView(buffer);

        // Verifica assinatura JPEG (FFD8)
        if (view.getUint16(0) !== 0xFFD8) return null;

        let offset = 2;

        // Percorre os segmentos do JPEG até achar APP1 (FFE1) com "Exif"
        while (offset < view.byteLength - 4) {
            const marker = view.getUint16(offset);
            offset += 2;

            if (marker === 0xFFE1) {
                const segLen = view.getUint16(offset);
                offset += 2;
                // Cabeçalho EXIF: "Exif\0\0"
                if (view.getUint32(offset) === 0x45786966 &&
                    view.getUint16(offset + 4) === 0x0000) {
                    return _parseTiffGps(view, offset + 6);
                }
                offset += segLen - 2;
            } else if ((marker & 0xFF00) === 0xFF00) {
                offset += view.getUint16(offset); // pula segmento
            } else {
                break;
            }
        }
        return null;
    } catch (_) {
        return null;
    }
}

function _parseTiffGps(view, base) {
    // Detecta byte order: 'II' = little-endian, 'MM' = big-endian
    const bo = view.getUint16(base);
    const le = (bo === 0x4949);

    const u16 = (o) => view.getUint16(o, le);
    const u32 = (o) => view.getUint32(o, le);

    // Offset do IFD0
    const ifd0 = base + u32(base + 4);
    const n0   = u16(ifd0);

    // Procura tag 0x8825 (GPS IFD offset) no IFD0
    let gpsBase = null;
    for (let i = 0; i < n0; i++) {
        const e = ifd0 + 2 + i * 12;
        if (u16(e) === 0x8825) { gpsBase = base + u32(e + 8); break; }
    }
    if (!gpsBase) return null;

    // Lê as tags GPS
    const nGps = u16(gpsBase);
    const tags = {};

    for (let i = 0; i < nGps; i++) {
        const e    = gpsBase + 2 + i * 12;
        const tag  = u16(e);
        const tipo = u16(e + 2);
        const qtd  = u32(e + 4);

        // Tag 1/3 = Ref (N/S/E/W) — ASCII
        if (tag === 1 || tag === 3) {
            tags[tag] = String.fromCharCode(view.getUint8(e + 8));
        }
        // Tag 2/4 = Lat/Lng — 3 racionais (numerador/denominador)
        if (tag === 2 || tag === 4) {
            const ptr  = base + u32(e + 8);
            const vals = [];
            for (let j = 0; j < 3; j++) {
                const num = u32(ptr + j * 8);
                const den = u32(ptr + j * 8 + 4);
                vals.push(den ? num / den : 0);
            }
            tags[tag] = vals;
        }
    }

    if (!tags[2] || !tags[4]) return null;

    const toDec = ([d, m, s]) => d + m / 60 + s / 3600;
    let lat = toDec(tags[2]);
    let lng = toDec(tags[4]);

    if (tags[1] === 'S') lat = -lat;
    if (tags[3] === 'W') lng = -lng;

    // Descarta se ficou zerado (GPS ausente ou corrompido)
    if (Math.abs(lat) < 0.0001 && Math.abs(lng) < 0.0001) return null;

    return { lat, lng };
}
