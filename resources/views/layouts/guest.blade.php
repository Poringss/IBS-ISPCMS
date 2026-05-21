<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ config('app.name', 'InsightBlitz') }}</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css.css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  @stack('styles')

  <style>
    body {
      background: #000;
      margin: 0;
      font-family: 'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #fff;
      overflow-x: hidden;
    }

    #lines-canvas-container {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      z-index: 1;
    }

    .content-overlay {
      position: relative;
      z-index: 10;
    }

    /* Shine border for register card */
    .shine-container {
      position: relative;
      padding: 2px;
      border-radius: 20px;
      overflow: hidden;
      display: inline-block;
    }
    .shine-border {
      position: absolute;
      top: -50%; left: -50%;
      width: 200%; height: 200%;
      background: conic-gradient(from 0deg, transparent 20%, #22c55e 25%, #4ade80 50%, #22c55e 75%, transparent 80%);
      animation: rotate-shine 4s linear infinite;
      z-index: 0;
    }
    @keyframes rotate-shine {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    .glass-card {
      position: relative;
      z-index: 2;
      max-width: 480px;
      width: 100%;
      background: rgba(6, 6, 12, 0.92);
      backdrop-filter: blur(20px);
      border-radius: 19px;
      box-shadow: 0 25px 60px rgba(0,0,0,0.5);
      color: #fff;
    }

    .swal2-popup {
      background: rgba(6, 6, 12, 0.95) !important;
      backdrop-filter: blur(16px);
      border: 1px solid rgba(34, 197, 94, 0.15) !important;
      color: #fff !important;
      border-radius: 16px !important;
    }

    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
  </style>
</head>
<body>

  <div id="lines-canvas-container"></div>

  <div class="content-overlay">
    <div class="min-vh-100 d-flex align-items-center justify-content-center px-3">
      @hasSection('raw-content')
        @yield('raw-content')
      @else
        
            <div class="p-4 p-sm-5">
              @yield('content')
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  {{-- THREE.js Animated Background --}}
  <script type="importmap">{ "imports": { "three": "https://unpkg.com/three@0.160.0/build/three.module.js" } }</script>
  <script type="module">
  import * as THREE from 'three';
  const vertexShader = `varying vec2 vUv; void main() { vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); }`;
  const fragmentShader = `
    precision highp float;
    uniform float iTime; uniform vec3 iResolution; uniform vec2 iMouse; uniform float bendInfluence;
    mat2 rotate(float r) { return mat2(cos(r), sin(r), -sin(r), cos(r)); }
    float wave(vec2 uv, float offset, vec2 screenUv, vec2 mouseUv, bool shouldBend) {
      float time = iTime * 1.2;
      float amp = sin(offset + time * 0.2) * 0.4;
      float y = sin(uv.x * 0.8 + offset + time * 0.15) * amp;
      if (shouldBend) {
        vec2 d = screenUv - mouseUv;
        y += (mouseUv.y - screenUv.y) * exp(-dot(d, d) * 4.0) * -0.6 * bendInfluence;
      }
      return 0.02 / max(abs(uv.y - y) + 0.01, 1e-3);
    }
    void main() {
      vec2 baseUv = (2.0 * gl_FragCoord.xy - iResolution.xy) / iResolution.y;
      baseUv.y *= -1.0;
      vec2 mouseUv = (2.0 * iMouse - iResolution.xy) / iResolution.y;
      mouseUv.y *= -1.0;
      vec3 col = vec3(0.0);
      vec3 GREEN = vec3(0.13, 0.77, 0.37);
      for (int i = 0; i < 3; ++i) {
        float fi = float(i);
        float opacity = 0.8 - (fi * 0.25);
        vec2 ruv = baseUv * rotate(0.15 * log(length(baseUv) + 1.2) + fi * 0.1);
        col += GREEN * wave(ruv + vec2(fi * 0.5, 0.0), 3.0 + fi * 1.5, baseUv, mouseUv, true) * opacity;
      }
      gl_FragColor = vec4(col, 1.0);
    }
  `;
  const container = document.getElementById('lines-canvas-container');
  if (container) {
    const scene = new THREE.Scene();
    const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);
    const uniforms = { iTime: { value: 0 }, iResolution: { value: new THREE.Vector3() }, iMouse: { value: new THREE.Vector2(-1000, -1000) }, bendInfluence: { value: 0 } };
    const material = new THREE.ShaderMaterial({ uniforms, vertexShader, fragmentShader });
    scene.add(new THREE.Mesh(new THREE.PlaneGeometry(2, 2), material));
    let targetInfluence = 0;
    window.addEventListener('pointermove', (e) => {
      const rect = container.getBoundingClientRect();
      uniforms.iMouse.value.set(e.clientX - rect.left, rect.height - (e.clientY - rect.top));
      targetInfluence = 1.0;
    });
    function animate(time) {
      uniforms.iTime.value = time * 0.001;
      uniforms.bendInfluence.value += (targetInfluence - uniforms.bendInfluence.value) * 0.05;
      const w = window.innerWidth, h = window.innerHeight;
      if (renderer.domElement.width !== w || renderer.domElement.height !== h) {
        renderer.setSize(w, h, false);
        uniforms.iResolution.value.set(w, h, 1);
      }
      renderer.render(scene, camera);
      requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
  }
  </script>

  @stack('scripts')
</body>
</html>
