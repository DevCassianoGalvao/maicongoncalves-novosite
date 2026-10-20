/*
 * Compressão de vídeo no próprio aparelho da pessoa (antes de enviar).
 * Usa WebCodecs pelo Mediabunny: converte para MP4 (H.264 + AAC), no máximo 60 s,
 * ou WebM (VP9 + Opus) nos navegadores que não codificam H.264,
 * lado maior até 960 px e ~1 Mbps. Um vídeo de 1 minuto fica com cerca de 8 MB.
 * Gerado por: cd tools && npm run video
 */
import { Input, Output, Conversion, BlobSource, BufferTarget, Mp4OutputFormat, WebMOutputFormat, ALL_FORMATS, canEncodeVideo, canEncodeAudio } from 'mediabunny';

export const MAX_SECONDS = 60;
const MAX_SIDE = 960;
const VIDEO_BPS = 1000000;
const AUDIO_BPS = 64000;

const even = (n) => Math.max(2, Math.round(n / 2) * 2);

/** Qual formato o navegador consegue gerar: 'mp4' (H.264), 'webm' (VP9) ou null. */
async function target() {
  if (typeof VideoEncoder === 'undefined' || typeof VideoDecoder === 'undefined') return null;
  const opt = { width: 960, height: 540, bitrate: VIDEO_BPS };
  try { if (await canEncodeVideo('avc', opt)) return 'mp4'; } catch (e) {}
  try { if (await canEncodeVideo('vp9', opt)) return 'webm'; } catch (e) {}
  return null;
}

/** O navegador consegue comprimir? */
export async function supported() { return (await target()) !== null; }

/** Primeiro quadro (em 0,5 s) como JPEG, para a capa do vídeo. */
export function poster(blob) {
  return new Promise((res) => {
    const v = document.createElement('video'), url = URL.createObjectURL(blob);
    let done = false;
    const end = (b) => { if (done) return; done = true; URL.revokeObjectURL(url); res(b); };
    v.muted = true; v.playsInline = true; v.preload = 'auto'; v.src = url;
    v.addEventListener('loadeddata', () => { v.currentTime = Math.min(0.5, (v.duration || 1) / 2); }, { once: true });
    v.addEventListener('seeked', () => {
      const s = Math.min(1, 960 / Math.max(v.videoWidth, v.videoHeight));
      const c = document.createElement('canvas'); c.width = even(v.videoWidth * s); c.height = even(v.videoHeight * s);
      c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
      c.toBlob((b) => end(b), 'image/jpeg', 0.82);
    }, { once: true });
    v.addEventListener('error', () => end(null), { once: true });
    setTimeout(() => end(null), 15000);
  });
}

/**
 * Comprime o vídeo. onProgress(0..1).
 * Retorna { blob, duration, width, height, trimmed, audio }.
 */
export async function compress(file, onProgress) {
  const input = new Input({ source: new BlobSource(file), formats: ALL_FORMATS });
  const track = await input.getPrimaryVideoTrack();
  if (!track) throw new Error('Não encontramos imagem nesse arquivo de vídeo.');
  const duration = await input.computeDuration();
  const end = Math.min(duration, MAX_SECONDS);
  const dw = track.displayWidth, dh = track.displayHeight;
  const s = Math.min(1, MAX_SIDE / Math.max(dw, dh));
  const width = even(dw * s), height = even(dh * s);

  const kind = await target();
  if (!kind) throw new Error('Este navegador não consegue preparar vídeos.');
  // áudio: AAC no MP4 (se o navegador não tiver, carrega o codificador AAC só agora); Opus no WebM
  let audioCodec = kind === 'mp4' ? 'aac' : 'opus';
  try {
    if (kind === 'mp4' && !(await canEncodeAudio('aac', { bitrate: AUDIO_BPS }))) {
      const m = await import('@mediabunny/aac-encoder');
      m.registerAacEncoder();
    }
    if (kind === 'webm' && !(await canEncodeAudio('opus', { bitrate: AUDIO_BPS }))) audioCodec = null;
  } catch (e) { audioCodec = null; }

  const format = kind === 'mp4' ? new Mp4OutputFormat({ fastStart: 'in-memory' }) : new WebMOutputFormat();
  const output = new Output({ format, target: new BufferTarget() });
  const conv = await Conversion.init({
    input, output,
    tracks: 'primary',
    trim: { start: 0, end },
    video: { width, height, fit: 'contain', codec: kind === 'mp4' ? 'avc' : 'vp9', bitrate: VIDEO_BPS, frameRate: 30, keyFrameInterval: 2, forceTranscode: true },
    audio: audioCodec ? { codec: audioCodec, bitrate: AUDIO_BPS, numberOfChannels: 1, sampleRate: audioCodec === 'aac' ? 44100 : 48000, forceTranscode: true } : { discard: true },
    showWarnings: false,
  });
  if (!conv.isValid) throw new Error('Este navegador não conseguiu preparar o vídeo.');
  const hasAudio = !conv.discardedTracks.some((d) => d.track && d.track.type === 'audio');
  if (onProgress) conv.onProgress = (p) => onProgress(p);
  await conv.execute();
  const blob = new Blob([output.target.buffer], { type: kind === 'mp4' ? 'video/mp4' : 'video/webm' });
  return { blob, duration: end, width, height, trimmed: duration > MAX_SECONDS + 0.5, audio: hasAudio };
}
