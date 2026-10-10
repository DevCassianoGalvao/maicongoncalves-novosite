import sys,glob
from PIL import Image
d,prefix,cols,scale=sys.argv[1],sys.argv[2],int(sys.argv[3]),float(sys.argv[4])
fs=sorted(glob.glob(f'{d}/{prefix}-[0-9]*.png')); ims=[Image.open(f).convert('RGB') for f in fs]
w,h=ims[0].size; w2,h2=int(w*scale),int(h*scale); rows=(len(ims)+cols-1)//cols
S=Image.new('RGB',(cols*(w2+8),rows*(h2+8)),(255,0,255))
for i,im in enumerate(ims): S.paste(im.resize((w2,h2),Image.LANCZOS),((i%cols)*(w2+8),(i//cols)*(h2+8)))
S.save(f'{d}/sheet-{prefix}.jpg',quality=84); print(prefix,S.size,len(ims))
