@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
    <div class="page-header"><h1>Site Settings</h1></div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        {{-- ============================================================ --}}
        {{-- BRANDING / LOGO --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Branding / Logo</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Site Logo</label>
                        @if(!empty($siteSettings['site_logo']))
                            <div class="settings-preview mb-1">
                                <img src="{{ url('media/' . $siteSettings['site_logo']) }}" alt="Logo" style="max-height:80px; border-radius:var(--radius); border:1px solid var(--border); padding:0.5rem; background:#fff;">
                                <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                    <input type="checkbox" name="remove_files[site_logo]" value="1"> Remove logo
                                </label>
                            </div>
                        @endif
                        <input type="file" name="file_settings[site_logo]" class="form-control" accept="image/*">
                        <input type="hidden" name="groups[site_logo]" value="branding">
                        <small class="text-muted">Recommended: PNG or SVG, max 5MB</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Favicon</label>
                        @if(!empty($siteSettings['site_favicon']))
                            <div class="settings-preview mb-1">
                                <img src="{{ url('media/' . $siteSettings['site_favicon']) }}" alt="Favicon" style="max-height:48px; border-radius:var(--radius); border:1px solid var(--border); padding:0.25rem; background:#fff;">
                                <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                    <input type="checkbox" name="remove_files[site_favicon]" value="1"> Remove favicon
                                </label>
                            </div>
                        @endif
                        <input type="file" name="file_settings[site_favicon]" class="form-control" accept="image/*">
                        <input type="hidden" name="groups[site_favicon]" value="branding">
                        <small class="text-muted">Recommended: 32x32 or 64x64 PNG</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- BACKGROUND IMAGES --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Background Images</div>
            <div class="card-body">
                <p class="text-muted text-small mb-2">Upload background images for different screen sizes. These appear behind the hero section and login/register pages.</p>

                <div class="form-group">
                    <label class="form-label">Desktop Background (1920x1080+)</label>
                    @if(!empty($siteSettings['bg_image_desktop']))
                        <div class="settings-preview mb-1">
                            <img src="{{ url('media/' . $siteSettings['bg_image_desktop']) }}" alt="Desktop BG" style="max-height:120px; width:100%; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                            <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                <input type="checkbox" name="remove_files[bg_image_desktop]" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <input type="file" name="file_settings[bg_image_desktop]" class="form-control" accept="image/*">
                    <input type="hidden" name="groups[bg_image_desktop]" value="branding">
                    <small class="text-muted">Recommended: 1920x1080 or larger, JPG/WebP</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Tablet Background (768x1024)</label>
                    @if(!empty($siteSettings['bg_image_tablet']))
                        <div class="settings-preview mb-1">
                            <img src="{{ url('media/' . $siteSettings['bg_image_tablet']) }}" alt="Tablet BG" style="max-height:120px; width:100%; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                            <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                <input type="checkbox" name="remove_files[bg_image_tablet]" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <input type="file" name="file_settings[bg_image_tablet]" class="form-control" accept="image/*">
                    <input type="hidden" name="groups[bg_image_tablet]" value="branding">
                    <small class="text-muted">Recommended: 768x1024, JPG/WebP</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Mobile Background (375x812)</label>
                    @if(!empty($siteSettings['bg_image_mobile']))
                        <div class="settings-preview mb-1">
                            <img src="{{ url('media/' . $siteSettings['bg_image_mobile']) }}" alt="Mobile BG" style="max-height:120px; width:100%; object-fit:cover; border-radius:var(--radius); border:1px solid var(--border);">
                            <label class="form-check mt-1" style="font-weight:400; font-size:0.8125rem;">
                                <input type="checkbox" name="remove_files[bg_image_mobile]" value="1"> Remove
                            </label>
                        </div>
                    @endif
                    <input type="file" name="file_settings[bg_image_mobile]" class="form-control" accept="image/*">
                    <input type="hidden" name="groups[bg_image_mobile]" value="branding">
                    <small class="text-muted">Recommended: 375x812 (portrait), JPG/WebP</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Background Overlay Color</label>
                        <input type="color" name="settings[bg_overlay_color]" class="form-control" value="{{ $siteSettings['bg_overlay_color'] ?? '#000000' }}" style="height:45px;">
                        <input type="hidden" name="groups[bg_overlay_color]" value="branding">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Background Overlay Opacity</label>
                        <input type="range" name="settings[bg_overlay_opacity]" class="form-control" min="0" max="100" step="5" value="{{ $siteSettings['bg_overlay_opacity'] ?? '70' }}" style="height:45px;" oninput="this.nextElementSibling.textContent = this.value + '%'">
                        <small class="text-muted">{{ ($siteSettings['bg_overlay_opacity'] ?? '70') }}%</small>
                        <input type="hidden" name="groups[bg_overlay_opacity]" value="branding">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Background Position</label>
                    <select name="settings[bg_position]" class="form-control">
                        @php $bgPos = $siteSettings['bg_position'] ?? 'center center'; @endphp
                        <option value="center center" @selected($bgPos === 'center center')>Center</option>
                        <option value="top center" @selected($bgPos === 'top center')>Top</option>
                        <option value="bottom center" @selected($bgPos === 'bottom center')>Bottom</option>
                        <option value="center left" @selected($bgPos === 'center left')>Left</option>
                        <option value="center right" @selected($bgPos === 'center right')>Right</option>
                    </select>
                    <input type="hidden" name="groups[bg_position]" value="branding">
                </div>

                <div class="form-group">
                    <label class="form-label">Background Size</label>
                    <select name="settings[bg_size]" class="form-control">
                        @php $bgSize = $siteSettings['bg_size'] ?? 'cover'; @endphp
                        <option value="cover" @selected($bgSize === 'cover')>Cover (fill entire area)</option>
                        <option value="contain" @selected($bgSize === 'contain')>Contain (show full image)</option>
                        <option value="auto" @selected($bgSize === 'auto')>Auto (original size)</option>
                    </select>
                    <input type="hidden" name="groups[bg_size]" value="branding">
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- THEME / COLORS --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Theme / Colors</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Primary Color</label>
                        <input type="color" name="settings[primary_color]" class="form-control" value="{{ $siteSettings['primary_color'] ?? '#e67e22' }}" style="height:45px;">
                        <input type="hidden" name="groups[primary_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Primary Dark Color</label>
                        <input type="color" name="settings[primary_dark_color]" class="form-control" value="{{ $siteSettings['primary_dark_color'] ?? '#d35400' }}" style="height:45px;">
                        <input type="hidden" name="groups[primary_dark_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Secondary Color</label>
                        <input type="color" name="settings[secondary_color]" class="form-control" value="{{ $siteSettings['secondary_color'] ?? '#2c3e50' }}" style="height:45px;">
                        <input type="hidden" name="groups[secondary_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Background Color</label>
                        <input type="color" name="settings[bg_color]" class="form-control" value="{{ $siteSettings['bg_color'] ?? '#f8f9fa' }}" style="height:45px;">
                        <input type="hidden" name="groups[bg_color]" value="theme">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Text Color</label>
                        <input type="color" name="settings[text_color]" class="form-control" value="{{ $siteSettings['text_color'] ?? '#2c3e50' }}" style="height:45px;">
                        <input type="hidden" name="groups[text_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Text Light Color</label>
                        <input type="color" name="settings[text_light_color]" class="form-control" value="{{ $siteSettings['text_light_color'] ?? '#7f8c8d' }}" style="height:45px;">
                        <input type="hidden" name="groups[text_light_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Card Color</label>
                        <input type="color" name="settings[card_color]" class="form-control" value="{{ $siteSettings['card_color'] ?? '#ffffff' }}" style="height:45px;">
                        <input type="hidden" name="groups[card_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Border Color</label>
                        <input type="color" name="settings[border_color]" class="form-control" value="{{ $siteSettings['border_color'] ?? '#dee2e6' }}" style="height:45px;">
                        <input type="hidden" name="groups[border_color]" value="theme">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Success Color</label>
                        <input type="color" name="settings[success_color]" class="form-control" value="{{ $siteSettings['success_color'] ?? '#27ae60' }}" style="height:45px;">
                        <input type="hidden" name="groups[success_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Danger Color</label>
                        <input type="color" name="settings[danger_color]" class="form-control" value="{{ $siteSettings['danger_color'] ?? '#e74c3c' }}" style="height:45px;">
                        <input type="hidden" name="groups[danger_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Warning Color</label>
                        <input type="color" name="settings[warning_color]" class="form-control" value="{{ $siteSettings['warning_color'] ?? '#f39c12' }}" style="height:45px;">
                        <input type="hidden" name="groups[warning_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Info Color</label>
                        <input type="color" name="settings[info_color]" class="form-control" value="{{ $siteSettings['info_color'] ?? '#3498db' }}" style="height:45px;">
                        <input type="hidden" name="groups[info_color]" value="theme">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Navbar Background Color</label>
                        <input type="color" name="settings[navbar_bg_color]" class="form-control" value="{{ $siteSettings['navbar_bg_color'] ?? '#2c3e50' }}" style="height:45px;">
                        <input type="hidden" name="groups[navbar_bg_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Navbar Text Color</label>
                        <input type="color" name="settings[navbar_text_color]" class="form-control" value="{{ $siteSettings['navbar_text_color'] ?? '#ffffff' }}" style="height:45px;">
                        <input type="hidden" name="groups[navbar_text_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Footer Background Color</label>
                        <input type="color" name="settings[footer_bg_color]" class="form-control" value="{{ $siteSettings['footer_bg_color'] ?? '#2c3e50' }}" style="height:45px;">
                        <input type="hidden" name="groups[footer_bg_color]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Footer Text Color</label>
                        <input type="color" name="settings[footer_text_color]" class="form-control" value="{{ $siteSettings['footer_text_color'] ?? '#ffffff' }}" style="height:45px;">
                        <input type="hidden" name="groups[footer_text_color]" value="theme">
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- TYPOGRAPHY / FONTS --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Typography / Fonts</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Body Font Family</label>
                        <select name="settings[font_family]" class="form-control">
                            @php $ff = $siteSettings['font_family'] ?? 'system'; @endphp
                            <option value="system" @selected($ff === 'system')>System Default</option>
                            <option value="'Inter', sans-serif" @selected($ff === "'Inter', sans-serif")>Inter</option>
                            <option value="'Roboto', sans-serif" @selected($ff === "'Roboto', sans-serif")>Roboto</option>
                            <option value="'Open Sans', sans-serif" @selected($ff === "'Open Sans', sans-serif")>Open Sans</option>
                            <option value="'Lato', sans-serif" @selected($ff === "'Lato', sans-serif")>Lato</option>
                            <option value="'Poppins', sans-serif" @selected($ff === "'Poppins', sans-serif")>Poppins</option>
                            <option value="'Montserrat', sans-serif" @selected($ff === "'Montserrat', sans-serif")>Montserrat</option>
                            <option value="'Nunito', sans-serif" @selected($ff === "'Nunito', sans-serif")>Nunito</option>
                            <option value="'Raleway', sans-serif" @selected($ff === "'Raleway', sans-serif")>Raleway</option>
                            <option value="'Source Sans 3', sans-serif" @selected($ff === "'Source Sans 3', sans-serif")>Source Sans</option>
                            <option value="'PT Sans', sans-serif" @selected($ff === "'PT Sans', sans-serif")>PT Sans</option>
                            <option value="'Merriweather', serif" @selected($ff === "'Merriweather', serif")>Merriweather (Serif)</option>
                            <option value="'Playfair Display', serif" @selected($ff === "'Playfair Display', serif")>Playfair Display (Serif)</option>
                            <option value="'Georgia', serif" @selected($ff === "'Georgia', serif")>Georgia (Serif)</option>
                            <option value="'Fira Code', monospace" @selected($ff === "'Fira Code', monospace")>Fira Code (Mono)</option>
                        </select>
                        <input type="hidden" name="groups[font_family]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heading Font Family</label>
                        <select name="settings[heading_font_family]" class="form-control">
                            @php $hff = $siteSettings['heading_font_family'] ?? 'inherit'; @endphp
                            <option value="inherit" @selected($hff === 'inherit')>Same as Body</option>
                            <option value="'Inter', sans-serif" @selected($hff === "'Inter', sans-serif")>Inter</option>
                            <option value="'Roboto', sans-serif" @selected($hff === "'Roboto', sans-serif")>Roboto</option>
                            <option value="'Open Sans', sans-serif" @selected($hff === "'Open Sans', sans-serif")>Open Sans</option>
                            <option value="'Lato', sans-serif" @selected($hff === "'Lato', sans-serif")>Lato</option>
                            <option value="'Poppins', sans-serif" @selected($hff === "'Poppins', sans-serif")>Poppins</option>
                            <option value="'Montserrat', sans-serif" @selected($hff === "'Montserrat', sans-serif")>Montserrat</option>
                            <option value="'Nunito', sans-serif" @selected($hff === "'Nunito', sans-serif")>Nunito</option>
                            <option value="'Raleway', sans-serif" @selected($hff === "'Raleway', sans-serif")>Raleway</option>
                            <option value="'Playfair Display', serif" @selected($hff === "'Playfair Display', serif")>Playfair Display (Serif)</option>
                            <option value="'Merriweather', serif" @selected($hff === "'Merriweather', serif")>Merriweather (Serif)</option>
                        </select>
                        <input type="hidden" name="groups[heading_font_family]" value="typography">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Custom Google Font URL</label>
                    <input type="text" name="settings[google_font_url]" class="form-control" value="{{ $siteSettings['google_font_url'] ?? '' }}" placeholder="e.g. https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
                    <input type="hidden" name="groups[google_font_url]" value="typography">
                    <small class="text-muted">Paste the full Google Fonts CSS URL. This loads the font so your selections above work.</small>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Base Font Size (px)</label>
                        <input type="number" name="settings[font_size_base]" class="form-control" min="12" max="24" step="1" value="{{ $siteSettings['font_size_base'] ?? '16' }}">
                        <input type="hidden" name="groups[font_size_base]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">H1 Size (rem)</label>
                        <input type="number" name="settings[font_size_h1]" class="form-control" min="1" max="5" step="0.125" value="{{ $siteSettings['font_size_h1'] ?? '1.75' }}">
                        <input type="hidden" name="groups[font_size_h1]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">H2 Size (rem)</label>
                        <input type="number" name="settings[font_size_h2]" class="form-control" min="1" max="4" step="0.125" value="{{ $siteSettings['font_size_h2'] ?? '1.5' }}">
                        <input type="hidden" name="groups[font_size_h2]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">H3 Size (rem)</label>
                        <input type="number" name="settings[font_size_h3]" class="form-control" min="0.875" max="3" step="0.125" value="{{ $siteSettings['font_size_h3'] ?? '1.25' }}">
                        <input type="hidden" name="groups[font_size_h3]" value="typography">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Body Font Weight</label>
                        <select name="settings[font_weight_body]" class="form-control">
                            @php $fwb = $siteSettings['font_weight_body'] ?? '400'; @endphp
                            <option value="300" @selected($fwb === '300')>300 - Light</option>
                            <option value="400" @selected($fwb === '400')>400 - Regular</option>
                            <option value="500" @selected($fwb === '500')>500 - Medium</option>
                            <option value="600" @selected($fwb === '600')>600 - Semi-Bold</option>
                        </select>
                        <input type="hidden" name="groups[font_weight_body]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Heading Font Weight</label>
                        <select name="settings[font_weight_heading]" class="form-control">
                            @php $fwh = $siteSettings['font_weight_heading'] ?? '700'; @endphp
                            <option value="500" @selected($fwh === '500')>500 - Medium</option>
                            <option value="600" @selected($fwh === '600')>600 - Semi-Bold</option>
                            <option value="700" @selected($fwh === '700')>700 - Bold</option>
                            <option value="800" @selected($fwh === '800')>800 - Extra-Bold</option>
                            <option value="900" @selected($fwh === '900')>900 - Black</option>
                        </select>
                        <input type="hidden" name="groups[font_weight_heading]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Line Height</label>
                        <input type="number" name="settings[line_height]" class="form-control" min="1" max="2.5" step="0.1" value="{{ $siteSettings['line_height'] ?? '1.6' }}">
                        <input type="hidden" name="groups[line_height]" value="typography">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Letter Spacing (px)</label>
                        <input type="number" name="settings[letter_spacing]" class="form-control" min="-2" max="5" step="0.25" value="{{ $siteSettings['letter_spacing'] ?? '0' }}">
                        <input type="hidden" name="groups[letter_spacing]" value="typography">
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- LAYOUT & SHAPE --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Layout & Shape</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Border Radius (px)</label>
                        <input type="number" name="settings[border_radius]" class="form-control" min="0" max="24" step="1" value="{{ $siteSettings['border_radius'] ?? '8' }}">
                        <input type="hidden" name="groups[border_radius]" value="theme">
                        <small class="text-muted">0 = sharp corners, 24 = very rounded</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Button Border Radius (px)</label>
                        <input type="number" name="settings[btn_border_radius]" class="form-control" min="0" max="50" step="1" value="{{ $siteSettings['btn_border_radius'] ?? '' }}" placeholder="Same as border radius">
                        <input type="hidden" name="groups[btn_border_radius]" value="theme">
                        <small class="text-muted">Leave blank to use main border radius. 50 = pill shape</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Card Shadow</label>
                        <select name="settings[card_shadow]" class="form-control">
                            @php $cs = $siteSettings['card_shadow'] ?? 'medium'; @endphp
                            <option value="none" @selected($cs === 'none')>None</option>
                            <option value="light" @selected($cs === 'light')>Light</option>
                            <option value="medium" @selected($cs === 'medium')>Medium (default)</option>
                            <option value="heavy" @selected($cs === 'heavy')>Heavy</option>
                        </select>
                        <input type="hidden" name="groups[card_shadow]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Container Max Width (px)</label>
                        <input type="number" name="settings[container_max_width]" class="form-control" min="900" max="1800" step="50" value="{{ $siteSettings['container_max_width'] ?? '1200' }}">
                        <input type="hidden" name="groups[container_max_width]" value="theme">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Button Style</label>
                        <select name="settings[btn_style]" class="form-control">
                            @php $bs = $siteSettings['btn_style'] ?? 'filled'; @endphp
                            <option value="filled" @selected($bs === 'filled')>Filled (default)</option>
                            <option value="outline" @selected($bs === 'outline')>Outline</option>
                            <option value="soft" @selected($bs === 'soft')>Soft / Tinted</option>
                        </select>
                        <input type="hidden" name="groups[btn_style]" value="theme">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Button Text Transform</label>
                        <select name="settings[btn_text_transform]" class="form-control">
                            @php $btt = $siteSettings['btn_text_transform'] ?? 'none'; @endphp
                            <option value="none" @selected($btt === 'none')>None (default)</option>
                            <option value="uppercase" @selected($btt === 'uppercase')>UPPERCASE</option>
                            <option value="capitalize" @selected($btt === 'capitalize')>Capitalize</option>
                        </select>
                        <input type="hidden" name="groups[btn_text_transform]" value="theme">
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- COMPANY INFO --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Company Info</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Company Name</label><input type="text" name="settings[company_name]" class="form-control" value="{{ $siteSettings['company_name'] ?? 'Concreto' }}"><input type="hidden" name="groups[company_name]" value="general"></div>
                <div class="form-group"><label class="form-label">Contact Email</label><input type="email" name="settings[contact_email]" class="form-control" value="{{ $siteSettings['contact_email'] ?? 'orders@concreto.co.za' }}"><input type="hidden" name="groups[contact_email]" value="general"></div>
                <div class="form-group"><label class="form-label">Contact Phone</label><input type="text" name="settings[contact_phone]" class="form-control" value="{{ $siteSettings['contact_phone'] ?? '' }}"><input type="hidden" name="groups[contact_phone]" value="general"></div>
                <div class="form-group"><label class="form-label">Contact Address</label><input type="text" name="settings[contact_address]" class="form-control" value="{{ $siteSettings['contact_address'] ?? '' }}"><input type="hidden" name="groups[contact_address]" value="general"></div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- HOMEPAGE --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Homepage</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Hero Title</label><input type="text" name="settings[hero_title]" class="form-control" value="{{ $siteSettings['hero_title'] ?? 'Quality Building Materials, Delivered' }}"><input type="hidden" name="groups[hero_title]" value="homepage"></div>
                <div class="form-group"><label class="form-label">Hero Subtitle</label><textarea name="settings[hero_subtitle]" class="form-control">{{ $siteSettings['hero_subtitle'] ?? '' }}</textarea><input type="hidden" name="groups[hero_subtitle]" value="homepage"></div>
                <div class="form-group"><label class="form-label">Delivery Info Banner</label><input type="text" name="settings[delivery_info]" class="form-control" value="{{ $siteSettings['delivery_info'] ?? '' }}"><input type="hidden" name="groups[delivery_info]" value="homepage"></div>
                <div class="form-group"><label class="form-label">Promo Banner</label><input type="text" name="settings[homepage_promo]" class="form-control" value="{{ $siteSettings['homepage_promo'] ?? '' }}"><input type="hidden" name="groups[homepage_promo]" value="homepage"></div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- FOOTER & SOCIAL --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Footer & Social</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Footer About Text</label><textarea name="settings[footer_about]" class="form-control">{{ $siteSettings['footer_about'] ?? '' }}</textarea><input type="hidden" name="groups[footer_about]" value="footer"></div>
                <div class="form-group"><label class="form-label">Footer Text</label><input type="text" name="settings[footer_text]" class="form-control" value="{{ $siteSettings['footer_text'] ?? '' }}"><input type="hidden" name="groups[footer_text]" value="footer"></div>
                <div class="form-group"><label class="form-label">Facebook URL</label><input type="url" name="settings[social_facebook]" class="form-control" value="{{ $siteSettings['social_facebook'] ?? '' }}"><input type="hidden" name="groups[social_facebook]" value="social"></div>
                <div class="form-group"><label class="form-label">Instagram URL</label><input type="url" name="settings[social_instagram]" class="form-control" value="{{ $siteSettings['social_instagram'] ?? '' }}"><input type="hidden" name="groups[social_instagram]" value="social"></div>
                <div class="form-group"><label class="form-label">Twitter / X URL</label><input type="url" name="settings[social_twitter]" class="form-control" value="{{ $siteSettings['social_twitter'] ?? '' }}"><input type="hidden" name="groups[social_twitter]" value="social"></div>
                <div class="form-group"><label class="form-label">WhatsApp Number</label><input type="text" name="settings[social_whatsapp]" class="form-control" value="{{ $siteSettings['social_whatsapp'] ?? '' }}" placeholder="+27..."><input type="hidden" name="groups[social_whatsapp]" value="social"></div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- LEGAL PAGES --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Legal Pages</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Terms & Conditions</label><textarea name="settings[terms_content]" class="form-control" rows="6">{{ $siteSettings['terms_content'] ?? '' }}</textarea><input type="hidden" name="groups[terms_content]" value="legal"></div>
                <div class="form-group"><label class="form-label">Privacy Policy</label><textarea name="settings[privacy_content]" class="form-control" rows="6">{{ $siteSettings['privacy_content'] ?? '' }}</textarea><input type="hidden" name="groups[privacy_content]" value="legal"></div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- BUSINESS RULES --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Business Rules</div>
            <div class="card-body">
                <div class="form-group"><label class="form-label">Minimum Order Value (R)</label><input type="number" name="settings[min_order_value]" class="form-control" step="0.01" value="{{ $siteSettings['min_order_value'] ?? '0' }}"><input type="hidden" name="groups[min_order_value]" value="business"></div>
                <div class="form-group"><label class="form-label">Business Hours</label><input type="text" name="settings[business_hours]" class="form-control" value="{{ $siteSettings['business_hours'] ?? 'Mon-Fri 07:00-17:00, Sat 07:00-12:00' }}"><input type="hidden" name="groups[business_hours]" value="business"></div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- CUSTOM CSS --}}
        {{-- ============================================================ --}}
        <div class="card">
            <div class="card-header">Custom CSS</div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Additional CSS</label>
                    <textarea name="settings[custom_css]" class="form-control" rows="8" style="font-family: 'Fira Code', monospace; font-size:0.8125rem;" placeholder="/* Add any custom CSS here */
.hero { background: linear-gradient(135deg, red, blue); }">{{ $siteSettings['custom_css'] ?? '' }}</textarea>
                    <input type="hidden" name="groups[custom_css]" value="theme">
                    <small class="text-muted">Advanced: Add raw CSS to override any styles. Be careful with this.</small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Save All Settings</button>
    </form>
@endsection
