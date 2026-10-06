[Lichen-Markdown](https://codeberg.org/ukrudt-net/lichen-markdown)을 github page 용으로 수정한 코드입니다.

Lichen-Markdown은 [yunohost](https://yunohost.org/) 등을 사용하여, 셀프 호스팅하기 쉽도록 만들어진 매우 가벼운 CMS 입니다.
요즘 시대에 셀프 호스팅 환경을 구축하는 것도 돈과 수고가 드는 일이기도 하고... codeberg에서 제공하는 codeberg-pages 기능도 제한되어 있는 상황이라 부득이하게도, Github Actions 환경에 맞게 불필요한 파일을 제거하고 몇가지 기능을 추가했습니다.

원본 프로젝트에서 추가된 기능은 다음과 같습니다.
- `assets` 폴더 구조 변경, image 파일 업로드시 `assets/image` 폴더에 업로드
- `layout.php` 파일에서 사용할 수 있는 컴포넌트 숏코드
- build, development 기능을 실행하는 Shell 파일 `run_build.sh`, `run_dev.sh`
- `cms/edit.php` 페이지 에디터에 markdown 코드 하이라이트 추가
- Github Action용 workflow
- 빌드 후, page_size를 보여주는 기능
- 빌드된 dist 폴더에서 변환된 html 파일들이 적절한 상대경로를 가지도록 함 (`markdown` 인터널 링크 -> html 상대 링크)