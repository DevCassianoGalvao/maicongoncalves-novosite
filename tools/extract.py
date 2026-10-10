import sys, re, json, html, glob, os
raw, out = sys.argv[1], sys.argv[2]
skip = {'home','minha-historia','fl360','descobrindo-talentos','noticias','contato'}
posts=[]
for f in sorted(glob.glob(os.path.join(raw,'*.html'))):
    slug=os.path.basename(f)[:-5]
    if slug in skip: continue
    s=open(f,encoding='utf-8',errors='replace').read()
    def m(p,flags=re.S):
        r=re.search(p,s,flags); return html.unescape(r.group(1).strip()) if r else None
    title=m(r'<h1[^>]*>(.*?)</h1>')
    title=re.sub(r'<[^>]+>','',title or '')
    og=m(r'property="og:image" content="([^"]+)"')
    desc=m(r'name="description" content="([^"]+)"')
    date=m(r'/(20\d\d/\d\d/\d\d)/"')
    cat=m(r'/category/[^"]+/"[^>]*>\s*(?:<[^>]+>\s*)*([^<]+)<')
    # body: theme-post-content widget
    b=re.search(r'data-widget_type="theme-post-content\.default">(.*?)</div>\s*<div class="elementor-element',s,re.S)
    body=b.group(1) if b else ''
    imgs=re.findall(r'<img[^>]+src="([^"]+)"',body)
    feat=re.search(r'theme-post-featured-image.*?<img[^>]+src="([^"]+)"',s,re.S)
    blocks=[]
    for tag,inner in re.findall(r'<(p|h2|h3|h4|li|blockquote|figcaption)\b[^>]*>(.*?)</\1>',body,re.S):
        t=html.unescape(re.sub(r'<[^>]+>','',inner)).strip()
        if t: blocks.append({'tag':tag,'text':t})
    posts.append(dict(slug=slug,title=title,category=cat,date=date,description=desc,og=og,featured=feat.group(1) if feat else None,body_images=imgs,blocks=blocks))
json.dump(posts,open(out,'w',encoding='utf-8'),ensure_ascii=False,indent=1)
for p in posts:
    print(p['date'],'|',p['category'],'|',p['title'][:60],'| blocos',len(p['blocks']),'| imgs',len(p['body_images']),'| feat',(p['featured'] or p['og'] or 'NONE').split('/')[-1])
