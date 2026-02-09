@extends('layouts.app')

@section('content')
    <div class="app-container" style="justify-content: center; align-items: center; padding: 2rem; overflow-y: auto;">
        <div class="modal-content glass-panel" style="max-width: 600px; transform: none; position: relative;">
            <div class="modal-header">
                <h2 style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fa-solid fa-user-tie"></i> Profil Pamekar
                </h2>
                <a href="/" class="close-modal" style="text-decoration: none;"><i class="fa-solid fa-house"></i></a>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div class="avatar"
                        style="width: 120px; height: 120px; margin: 0 auto 1rem; border-width: 2px; overflow: hidden;">
                        <img src="{{ asset('images/rflmoraa.jpeg') }}" alt="Rifal Kurniawan"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h1 style="color: var(--primary); margin-bottom: 0.25rem;">Rifal Kurniawan</h1>
                    <p style="color: var(--text-muted); font-style: italic;">Full Stack Developer & Cultural Enthusiast</p>
                </div>

                <div style="display: grid; gap: 1.5rem;">
                    <section>
                        <h3
                            style="color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-circle-info"></i> Ngeunaan Abdi
                        </h3>
                        <p style="line-height: 1.6; font-size: 0.95rem;">
                            Sim kuring nyaéta saurang pamekar parangkat lunak (software developer) anu gaduh kapeuyeum dina
                            ngahijikeun téknologi modérn sareng kabeungharan budaya lokal, khususna budaya Sunda. Proyék
                            <strong>Pananyaan Abah</strong> ieu mangrupikeun salah sahiji wujud kacinta sim kuring kana Basa
                            Karuhun.
                        </p>
                    </section>

                    <section>
                        <h3
                            style="color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-microchip"></i> Kaahlihan Téknis
                        </h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">PHP
                                8+</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">Laravel</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">Livewire</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">MySQL</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">JavaScript</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">Alpine.js</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">Tailwind
                                CSS</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">AI
                                & API Integration</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">Git
                                & GitHub</span>
                            <span
                                style="background: rgba(212, 175, 55, 0.1); border: 1px solid var(--border); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.85rem;">Bootstrap</span>
                        </div>
                    </section>

                    <section>
                        <h3
                            style="color: var(--primary); border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 0.75rem;">
                            <i class="fa-solid fa-envelope"></i> Kontak
                        </h3>
                        <ul style="list-style: none; padding: 0; display: grid; gap: 0.5rem;">
                            <li style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fa-brands fa-github" style="color: var(--primary); width: 20px;"></i>
                                <a href="https://github.com/Rifal21"
                                    style="color: var(--text-main); text-decoration: none;">github.com/Rifal21</a>
                            </li>
                            <li style="display: flex; align-items: center; gap: 0.75rem;">
                                <i class="fa-solid fa-envelope" style="color: var(--primary); width: 20px;"></i>
                                <a href="mailto:rifalkurniawan289@gmail.com"
                                    style="color: var(--text-main); text-decoration: none;">rifalkurniawan289@gmail.com</a>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <a href="/" class="btn-primary" style="text-decoration: none;">Balik Kana Paguneman</a>
            </div>
        </div>
    </div>
@endsection
